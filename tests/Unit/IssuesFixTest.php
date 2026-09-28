<?php

declare(strict_types=1);

use YandexMapsLocator\Extension\FeatureProviderInterface;
use YandexMapsLocator\Extension\FeatureProviderRegistry;
use YandexMapsLocator\Filter\FilterManager;
use YandexMapsLocator\Frontend\SearchHandler;
use YandexMapsLocator\Frontend\SearchSecurity;
use YandexMapsLocator\Model\SearchCriteria;
use YandexMapsLocator\Repository\ResourceStoreRepository;
use YandexMapsLocator\Repository\TvValueLoader;
use YandexMapsLocator\Service\CacheService;
use YandexMapsLocator\Support\RateLimiter;
use YandexMapsLocator\Support\TvNames;
use YandexMapsLocator\YandexMapsLocator;

/**
 * Recording xPDO query stub: captures where() calls so buildResourceQuery
 * can be asserted without a real database.
 */
final class RecordingQuery
{
    /** @var list<mixed> */
    public array $whereCalls = [];

    public function where(mixed $conditions): self
    {
        $this->whereCalls[] = $conditions;

        return $this;
    }

    public function sortby(string $field, string $dir = 'ASC'): self
    {
        return $this;
    }

    public function limit(int $limit, int $offset = 0): self
    {
        return $this;
    }
}

/**
 * Minimal feature provider that advertises the `pro` capability, used to
 * exercise YandexMapsLocator::getMapConfig() with REST enabled.
 */
final class ProFeatureProvider implements FeatureProviderInterface
{
    public function capabilities(): array
    {
        return ['pro'];
    }

    public function frontendModules(): array
    {
        return [];
    }

    public function processorActions(): array
    {
        return [];
    }

    public function apiFields(): array
    {
        return [];
    }
}

/**
 * Issue #1 — `&where` JSON from the snippet must merge with the mandatory
 * resource conditions (parent/published/deleted/context), not be overwritten.
 */
describe('ResourceStoreRepository buildResourceQuery where merge', function () {
    $buildQuery = function (SearchCriteria $criteria): RecordingQuery {
        $modx = Mockery::mock(\MODX\Revolution\modX::class);
        $modx->context = null;
        $modx->shouldReceive('invokeEvent')->andReturn([]);
        $query = new RecordingQuery();
        $modx->shouldReceive('newQuery')->andReturn($query);
        $modx->shouldReceive('getOption')->andReturnUsing(
            static fn (string $key, $opts = null, $default = null) => $default,
        );
        $modx->shouldReceive('log');

        $repository = new ResourceStoreRepository(
            $modx,
            new TvNames($modx),
            new FilterManager($modx),
            new TvValueLoader($modx),
        );

        $method = new ReflectionMethod($repository, 'buildResourceQuery');

        return $method->invoke($repository, $criteria, [5]);
    };

    it('applies mandatory conditions even without user where', function () use ($buildQuery) {
        $criteria = SearchCriteria::fromArray(['parents' => '5']);

        $query = $buildQuery($criteria);

        expect($query->whereCalls)->toHaveCount(1)
            ->and($query->whereCalls[0])->toHaveKey('parent:IN')
            ->and($query->whereCalls[0])->toHaveKey('published')
            ->and($query->whereCalls[0])->toHaveKey('deleted')
            ->and($query->whereCalls[0])->toHaveKey('context_key');
    });

    it('merges snippet where on top of mandatory conditions', function () use ($buildQuery) {
        $criteria = SearchCriteria::fromArray([
            'parents' => '5',
            'where' => '{"template":5}',
        ]);

        $query = $buildQuery($criteria);

        expect($query->whereCalls)->toHaveCount(2)
            ->and($query->whereCalls[0])->toHaveKey('parent:IN')
            ->and($query->whereCalls[1])->toBe(['template' => 5]);
    });
});

/**
 * Issue #2 — the Bearer api_token must never leak into page HTML. When a
 * secret token is configured, the on-page locator falls back to same-origin
 * search.php; REST with Bearer stays for server-side clients (Nuxt BFF).
 */
describe('YandexMapsLocator getMapConfig token leak', function () {
    $makeLocator = function (string $apiToken): YandexMapsLocator {
        $modx = Mockery::mock(\MODX\Revolution\modX::class);
        $modx->shouldReceive('invokeEvent')->andReturnUsing(
            static function (string $event, array $params = []): array {
                if ($event === 'OnYandexMapsLocatorRegisterFeatureProviders'
                    && isset($params['registry'])
                    && $params['registry'] instanceof FeatureProviderRegistry
                ) {
                    $params['registry']->add(new ProFeatureProvider());
                }

                return [];
            },
        );
        $modx->shouldReceive('getOption')->andReturnUsing(
            static function (string $key, $opts = null, $default = null) use ($apiToken) {
                return match ($key) {
                    'yandexmapslocator_api_enabled' => true,
                    'yandexmapslocator_api_token' => $apiToken,
                    'yandexmapslocatorpro.assets_url' => '/assets/components/yandexmapslocatorpro/',
                    default => $default,
                };
            },
        );

        return new YandexMapsLocator($modx);
    };

    it('never exposes apiToken and disables REST when secret token is set', function () use ($makeLocator) {
        $config = $makeLocator('secret-bearer-123')->getMapConfig();

        expect($config)->not->toHaveKey('apiToken')
            ->and($config['restApi'])->toBeFalse()
            ->and($config)->not->toHaveKey('apiUrl');
    });

    it('keeps REST enabled without a token (public dev mode) and still never exposes apiToken', function () use ($makeLocator) {
        $config = $makeLocator('')->getMapConfig();

        expect($config)->not->toHaveKey('apiToken')
            ->and($config['restApi'])->toBeTrue()
            ->and($config['apiUrl'])->toBe('/assets/components/yandexmapslocatorpro/api.php');
    });
});

/**
 * Issue #3 — search.php must consume the `geocode` rate-limit bucket when an
 * address is supplied (triggers the Yandex geocoder), mirroring REST
 * LocationsController. Without address, only the `search` bucket is spent.
 */
describe('SearchHandler geocode rate limit', function () {
    $makeLocator = function (): YandexMapsLocator {
        $modx = Mockery::mock(\MODX\Revolution\modX::class);
        $modx->context = null;
        $modx->shouldReceive('invokeEvent')->andReturn([]);
        $modx->shouldReceive('getOption')->andReturnUsing(
            static fn (string $key, $opts = null, $default = null) => $default,
        );
        $modx->shouldReceive('log');

        return new YandexMapsLocator($modx);
    };

    $backupServer = $_SERVER;
    $backupGet = $_GET;

    beforeEach(function () use (&$backupServer, &$backupGet) {
        $backupServer = $_SERVER;
        $backupGet = $_GET;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
    });

    afterEach(function () use (&$backupServer, &$backupGet) {
        $_SERVER = $backupServer;
        $_GET = $backupGet;
    });

    it('consumes the geocode bucket when address is supplied', function () use ($makeLocator) {
        $locator = $makeLocator();
        $spy = Mockery::mock(SearchSecurity::class, [$locator]);
        $spy->shouldReceive('assertRateLimit')->with('search')->once();
        $spy->shouldReceive('assertRateLimit')->with('geocode')
            ->once()
            ->andThrow(new \RuntimeException('geocode_bucket_consumed'));
        $spy->shouldReceive('handlePreflight');

        $_GET = ['parents' => '5', 'address' => 'Москва'];

        $handler = new SearchHandler($locator, $spy);

        expect(fn () => $handler->handle())->toThrow(\RuntimeException::class, 'geocode_bucket_consumed');
    });

    it('does not consume the geocode bucket without an address', function () use ($makeLocator) {
        $locator = $makeLocator();
        $spy = Mockery::mock(SearchSecurity::class, [$locator]);
        $spy->shouldReceive('assertRateLimit')->with('geocode')->never();
        $spy->shouldReceive('assertRateLimit')->with('search')
            ->once()
            ->andThrow(new \RuntimeException('search_only'));
        $spy->shouldReceive('handlePreflight');

        $_GET = ['parents' => '5'];

        $handler = new SearchHandler($locator, $spy);

        expect(fn () => $handler->handle())->toThrow(\RuntimeException::class, 'search_only');
    });
});

/**
 * Issue #3 building block — RateLimiter enforces the `geocode` bucket so that
 * SearchSecurity::assertRateLimit('geocode') can return 429 over budget.
 */
describe('RateLimiter geocode bucket', function () {
    it('blocks once the per-minute geocode budget is exhausted', function () {
        $modx = Mockery::mock(\MODX\Revolution\modX::class);
        $modx->shouldReceive('getOption')->andReturnUsing(
            static fn (string $key, $opts = null, $default = null) => $default,
        );
        $cacheManager = Mockery::mock(\stdClass::class);
        $cacheManager->shouldReceive('get')->andReturn(30);
        $modx->cacheManager = $cacheManager;

        $_SERVER['REMOTE_ADDR'] = '8.8.8.8';

        $limiter = new RateLimiter($modx, new CacheService($modx));

        expect($limiter->check('geocode', $limiter->geocodeLimit()))->toBeFalse();
    });
});
