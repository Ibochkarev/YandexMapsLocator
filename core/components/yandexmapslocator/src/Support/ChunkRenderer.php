<?php

declare(strict_types=1);

namespace YandexMapsLocator\Support;

use MODX\Revolution\modX;
use YandexMapsLocator\Model\Store;

final class ChunkRenderer
{
    private static bool $pdoToolsWarningLogged = false;

    public function __construct(private readonly modX $modx)
    {
    }

    public function ensureLexiconLoaded(): void
    {
        $topic = 'yandexmapslocator:default';
        $this->modx->lexicon->load($topic);

        $sentinel = 'yandexmapslocator_show_on_map';
        if ($this->modx->lexicon->exists($sentinel)) {
            return;
        }

        $cultureKey = (string) $this->modx->getOption('cultureKey', null, 'en');
        $this->modx->lexicon->clearCache('topics/lexicon/' . $cultureKey . '/yandexmapslocator');

        $entries = $this->modx->lexicon->getFileTopic($cultureKey, 'yandexmapslocator', 'default');
        if (!is_array($entries) || $entries === []) {
            $entries = $this->modx->lexicon->getFileTopic('en', 'yandexmapslocator', 'default');
        }

        if (is_array($entries) && $entries !== []) {
            $this->modx->lexicon->set($entries, '', $cultureKey);
        }
    }

    /**
     * @param array<string, mixed> $properties
     */
    public function render(string $name, array $properties = []): string
    {
        if ($name === '') {
            return '';
        }

        $pdoTools = $this->resolvePdoTools();
        if ($pdoTools !== null) {
            return (string) $pdoTools->getChunk($name, $properties);
        }

        $this->warnIfPdoToolsMissing();

        return (string) $this->modx->getChunk($name, $properties);
    }

    /**
     * @param list<Store> $stores
     */
    public function renderStores(array $stores, string $tpl, string $distanceUnit = 'km'): string
    {
        $output = '';
        foreach ($stores as $store) {
            if (!$store instanceof Store) {
                continue;
            }

            $output .= $this->render($tpl, $store->toArray($distanceUnit));
        }

        return $output;
    }

    /**
     * @return object{getChunk: callable(string, array<string, mixed>): string}|null
     */
    private function resolvePdoTools(): ?object
    {
        foreach (['ModxPro\\PdoTools\\CoreTools', 'pdoTools'] as $serviceId) {
            if (!$this->modx->services->has($serviceId)) {
                continue;
            }

            $service = $this->modx->services->get($serviceId);
            if (is_object($service) && method_exists($service, 'getChunk')) {
                return $service;
            }
        }

        if (method_exists($this->modx, 'getService')) {
            $service = $this->modx->getService('pdoTools');
            if (is_object($service) && method_exists($service, 'getChunk')) {
                return $service;
            }
        }

        return null;
    }

    private function warnIfPdoToolsMissing(): void
    {
        if (self::$pdoToolsWarningLogged) {
            return;
        }

        self::$pdoToolsWarningLogged = true;
        $this->modx->log(
            modX::LOG_LEVEL_WARN,
            '[YandexMapsLocator] Fenom chunks require pdoTools. Install pdoTools or chunk output will be broken.',
        );
    }
}
