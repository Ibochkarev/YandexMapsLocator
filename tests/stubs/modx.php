<?php

declare(strict_types=1);

namespace MODX\Revolution;

class modX
{
    public const LOG_LEVEL_ERROR = 1;
    public const LOG_LEVEL_WARN = 2;
    public const LOG_LEVEL_INFO = 3;

    /** @var object{has(string): bool, get(string): mixed, add(string, callable): void} */
    public $services;
    public $cacheManager;
    public $lexicon;
    public $request;
    public $user;
    public $context;
    public $event;

    /** @var array<string, string> */
    private array $chunkTemplates = [];

    public function __construct()
    {
        $this->services = new class () {
            public function has(string $key): bool
            {
                return false;
            }

            public function get(string $key): mixed
            {
                return null;
            }

            public function add(string $key, callable $factory): void
            {
            }
        };
        $this->lexicon = new class () {
            public function load(string $topic): void
            {
            }
        };
    }

    public function lexicon(string $key, array $options = []): string
    {
        return $key;
    }

    public function initialize(string $contextKey = 'web'): bool
    {
        return true;
    }

    public function getOption(string $key, $options = null, $default = null, bool $skipEvents = false)
    {
        return $default;
    }

    public function setOption(string $key, mixed $value, bool $cacheFlag = false): bool
    {
        return true;
    }

    public function getObject(string $className, $criteria = null, bool $cacheFlag = true)
    {
        return null;
    }

    public function newQuery(string $className, $criteria = null)
    {
        return new \xPDOQueryStub();
    }

    public function getCollection(string $className, $criteria = null, bool $cacheFlag = true): array
    {
        return [];
    }

    public function getCount(string $className, $criteria = null): int
    {
        return 0;
    }

    public function newObject(string $className, array $data = []): object
    {
        return new \stdClass();
    }

    public function makeUrl(string $id, string $args = '', string $scheme = 'full', array $options = []): string
    {
        return '/';
    }

    public function getChunk(string $name, array $properties = []): string
    {
        $template = $this->chunkTemplates[$name] ?? '';

        $replacements = [];
        foreach ($properties as $key => $value) {
            if (is_scalar($value)) {
                $replacements['{$' . $key . '}'] = (string) $value;
            }
        }

        return strtr($template, $replacements);
    }

    public function setChunkTemplate(string $name, string $template): void
    {
        $this->chunkTemplates[$name] = $template;
    }

    public function setPlaceholder(string $key, mixed $value): void
    {
    }

    public function invokeEvent(string $eventName, array $params = []): mixed
    {
        return [];
    }

    public function log(int $level, string $msg, string $target = '', string $def = '', string $file = '', string $line = ''): void
    {
    }

    public function prepare(string $sql): \PDOStatement|false
    {
        return false;
    }

    public function quote(string $value): string
    {
        return "'" . addslashes($value) . "'";
    }

    public function hasPermission(string $permission): bool
    {
        return true;
    }

    public function getRequest(): void
    {
    }

    public function getManager(): object
    {
        return new \stdClass();
    }
}

class modResource
{
    public function get(string $key): mixed
    {
        return null;
    }

    public function getTVValue(string $name): mixed
    {
        return null;
    }

    public function fromArray(array $data, string $prefix = '', bool $merge = true, bool $recursive = false): void
    {
    }

    public function save(): bool
    {
        return true;
    }

    public function setTVValue(string $name, mixed $value): void
    {
    }
}

class modSystemSetting
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function save(): bool
    {
        return true;
    }

    public function fromArray(array $data, string $prefix = '', bool $merge = true, bool $recursive = false): void
    {
    }
}

class modConnectorRequest
{
    public function handleRequest(array $options = []): void
    {
    }
}

namespace MODX\Revolution\Processors;

class Processor
{
    protected \MODX\Revolution\modX $modx;

    public function failure(string $message = '', mixed $object = null, array $options = []): array
    {
        return ['success' => false, 'message' => $message];
    }

    public function success(string $message = '', mixed $object = null): array
    {
        return ['success' => true, 'message' => $message, 'object' => $object];
    }

    public function getProperty(string $key, mixed $default = null): mixed
    {
        return $default;
    }

    public function process()
    {
        return $this->failure('Not implemented');
    }
}

class modCategory
{
    public function set(string $key, mixed $value): void
    {
    }

    public function addMany(array $objects): void
    {
    }
}

class modSystemSetting
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function save(): bool
    {
        return true;
    }

    public function fromArray(array $data, string $prefix = '', bool $merge = true, bool $recursive = false): void
    {
    }
}

class modSnippet
{
    public function fromArray(array $data, string $prefix = '', bool $merge = true, bool $recursive = false): void
    {
    }

    public function setProperties(mixed $properties): void
    {
    }
}

class modChunk
{
    public function fromArray(array $data, string $prefix = '', bool $merge = true, bool $recursive = false): void
    {
    }

    public function setProperties(mixed $properties): void
    {
    }
}

class modPlugin
{
    public function fromArray(array $data, string $prefix = '', bool $merge = true, bool $recursive = false): void
    {
    }

    public function addMany(array $objects): void
    {
    }
}

class modPluginEvent
{
    public function fromArray(array $data, string $prefix = '', bool $merge = true, bool $recursive = false): void
    {
    }
}

class modTemplate
{
    public function fromArray(array $data, string $prefix = '', bool $merge = true, bool $recursive = false): void
    {
    }
}

class modTemplateVar
{
    public function fromArray(array $data, string $prefix = '', bool $merge = true, bool $recursive = false): void
    {
    }

    public function save(): bool
    {
        return true;
    }
}

class modEvent
{
    public function fromArray(array $data, string $prefix = '', bool $merge = true, bool $recursive = false): void
    {
    }

    public function save(): bool
    {
        return true;
    }
}

class modMenu
{
    public function fromArray(array $data, string $prefix = '', bool $merge = true, bool $recursive = false): void
    {
    }
}

class modDashboardWidget
{
    public function fromArray(array $data, string $prefix = '', bool $merge = true, bool $recursive = false): void
    {
    }
}

class modAccessPolicy
{
    public function fromArray(array $data, string $prefix = '', bool $merge = true, bool $recursive = false): void
    {
    }
}

class modAccessPermission
{
    public function fromArray(array $data, string $prefix = '', bool $merge = true, bool $recursive = false): void
    {
    }
}

class modAccessPolicyTemplate
{
    public function fromArray(array $data, string $prefix = '', bool $merge = true, bool $recursive = false): void
    {
    }
}

namespace MODX\Revolution\Transport;

class modPackageBuilder
{
    public function __construct(modX $modx)
    {
    }

    public function createPackage(string $name, string $version, string $release): void
    {
    }

    public function registerNamespace(string $name, bool $path, bool $assets, string $pathSetting): void
    {
    }

    public function createVehicle(object $object, array $attributes = []): object
    {
        return new \TransportVehicleStub();
    }

    public function putVehicle(object $vehicle): void
    {
    }

    public function setPackageAttributes(array $attributes): void
    {
    }

    public function pack(): void
    {
    }

    public function getSignature(): string
    {
        return 'yandexmapslocator-1.0.0-pl';
    }
}

class modTransportPackage
{
    public $xpdo;

    public function fromArray(array $data, string $prefix = '', bool $merge = true, bool $recursive = false): void
    {
    }

    public function set(string $key, mixed $value): void
    {
    }

    public function save(): bool
    {
        return true;
    }

    public function install(): bool
    {
        return true;
    }
}

class xPDOQueryStub
{
    public function where(mixed $conditions): self
    {
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

class TransportVehicleStub
{
    public function resolve(string $type, array $options): bool
    {
        return true;
    }
}

namespace xPDO\Transport;

class xPDOTransport
{
    public const UNIQUE_KEY = 'unique_key';
    public const PRESERVE_KEYS = 'preserve_keys';
    public const UPDATE_OBJECT = 'update_object';
    public const RELATED_OBJECTS = 'related_objects';
    public const RELATED_OBJECT_ATTRIBUTES = 'related_object_attributes';
    public const ACTION_INSTALL = 'install';
    public const ACTION_UPGRADE = 'upgrade';
    public const ACTION_UNINSTALL = 'uninstall';
    public const PACKAGE_ACTION = 'package_action';

    public $xpdo;
}

namespace xPDO\Om;

class xPDOObject
{
}
