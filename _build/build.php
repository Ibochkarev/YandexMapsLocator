<?php

use MODX\Revolution\modX;
use MODX\Revolution\modCategory;
use MODX\Revolution\Transport\modPackageBuilder;
use MODX\Revolution\Transport\modTransportPackage;
use MODX\Revolution\modSystemSetting;
use MODX\Revolution\modMenu;
use MODX\Revolution\modSnippet;
use MODX\Revolution\modDashboardWidget;
use MODX\Revolution\modPlugin;
use MODX\Revolution\modPluginEvent;
use MODX\Revolution\modChunk;
use MODX\Revolution\modTemplate;
use MODX\Revolution\modAccessPolicy;
use MODX\Revolution\modAccessPermission;
use MODX\Revolution\modAccessPolicyTemplate;

class YandexMapsLocatorPackage
{
    private $modx;
    private $config = [];
    private $category;
    private $category_attributes = [];

    public $builder;

    /**
     * YandexMapsLocatorPackage constructor.
     *
     * @param modX $modx
     * @param array $config
     */
    public function __construct(modX $modx, array $config = [])
    {
        $this->modx = $modx;
        $this->modx->initialize('mgr');

        $root = dirname(__FILE__, 2) . '/';
        $core = $root . 'core/components/' . $config['name_lower'] . '/';
        $assets = $root . 'assets/components/' . $config['name_lower'] . '/';

        $this->config = array_merge([
            'log_level' => modX::LOG_LEVEL_INFO,
            'log_target' => XPDO_CLI_MODE ? 'ECHO' : 'HTML',

            'root' => $root,
            'build' => $root . '_build/',
            'elements' => $root . '_build/elements/',
            'resolvers' => $root . '_build/resolvers/',
            'core' => $core,
            'assets' => $assets,
        ], $config);
        $this->modx->setLogLevel($this->config['log_level']);
        $this->modx->setLogTarget($this->config['log_target']);

        $this->initialize();
    }

    /**
     * @return modPackageBuilder
     */
    public function process()
    {
        $this->runComposerInstall();
        $this->buildModel();
        $this->assets();

        // Add elements
        $elements = scandir($this->config['elements']);
        foreach ($elements as $element) {
            if (in_array($element[0], ['_', '.'])) {
                continue;
            }
            $name = preg_replace('#\.php$#', '', $element);
            if (method_exists($this, $name)) {
                $this->{$name}();
            }
        }

        // Create main vehicle
        $vehicle = $this->builder->createVehicle($this->category, $this->category_attributes);

        // Files resolvers
        $vehicle->resolve('file', [
            'source' => $this->config['core'],
            'target' => "return MODX_CORE_PATH . 'components/';",
        ]);
        $vehicle->resolve('file', [
            'source' => $this->config['assets'],
            'target' => "return MODX_ASSETS_PATH . 'components/';",
        ]);

        // Resolvers: tables before presets (scandir alone would run presets.php first).
        $resolverDir = $this->config['resolvers'];
        $preferredResolvers = [
            'settings.php',
            'tvs.php',
            'events.php',
            'symlinks.php',
        ];
        $foundResolvers = [];
        foreach (scandir($resolverDir) ?: [] as $resolver) {
            if ($resolver[0] === '.' || $resolver[0] === '_' || !str_ends_with($resolver, '.php')) {
                continue;
            }
            $foundResolvers[] = $resolver;
        }
        $orderedResolvers = [];
        foreach ($preferredResolvers as $resolver) {
            if (in_array($resolver, $foundResolvers, true)) {
                $orderedResolvers[] = $resolver;
            }
        }
        foreach ($foundResolvers as $resolver) {
            if (!in_array($resolver, $orderedResolvers, true)) {
                $orderedResolvers[] = $resolver;
            }
        }
        foreach ($orderedResolvers as $resolver) {
            if ($vehicle->resolve('php', ['source' => $resolverDir . $resolver])) {
                $this->modx->log(modX::LOG_LEVEL_INFO, 'Added resolver ' . preg_replace('#\.php$#', '', $resolver));
            }
        }

        $this->builder->putVehicle($vehicle);

        $this->builder->setPackageAttributes([
            'changelog' => file_get_contents($this->config['core'] . 'docs/changelog.txt'),
            'license' => file_get_contents($this->config['core'] . 'docs/license.txt'),
            'readme' => file_get_contents($this->config['core'] . 'docs/readme.txt'),
            'requires' => [
                'php' => '>=8.2.0',
                'modx' => '>=3.0.0',
            ],
        ]);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Added package attributes and setup options.');

        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packing up transport package zip...');
        $this->builder->pack();

        if (!empty($this->config['install'])) {
            $this->install();
        }

        return $this->builder;
    }


    /**
     * Initialize package builder
     */
    private function initialize()
    {
        $this->builder = new modPackageBuilder($this->modx);
        $this->builder->createPackage($this->config['name_lower'], $this->config['version'], $this->config['release']);
        $this->builder->registerNamespace($this->config['name_lower'], false, true, '{core_path}components/' . $this->config['name_lower'] . '/');
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Created Transport Package and Namespace.');

        $this->category = $this->modx->newObject(modCategory::class);
        $this->category->set('category', $this->config['name']);
        $this->category_attributes = [
            xPDOTransport::UNIQUE_KEY => 'category',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [],
        ];
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Created main Category.');
    }


    /**
     * Run composer install in component directory so vendor/ is included in the package.
     */
    private function runComposerInstall()
    {
        $composerJson = $this->config['core'] . 'composer.json';
        if (!file_exists($composerJson)) {
            return;
        }
        $cmd = 'cd ' . escapeshellarg($this->config['core']) . ' && composer install --no-dev --no-interaction 2>&1';
        $output = shell_exec($cmd);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Composer install: ' . trim((string) $output));
    }

    /**
     * Update the model
     */
    private function buildModel()
    {
        $schemaFile = $this->config['core'] . 'schema/' . $this->config['name_lower'] . '.mysql.schema.xml';
        $outputDir = $this->config['core'] . 'src/';
        if (!file_exists($schemaFile) || empty(file_get_contents($schemaFile))) {
            return;
        }

        $manager = $this->modx->getManager();
        $generator = $manager->getGenerator();
        $generator->parseSchema(
            $schemaFile,
            $outputDir,
            [
                "compile" => 0,
                "update" => 1,
                "regenerate" => 1,
                "namespacePrefix" => "YandexMapsLocator\\"
            ]
        );
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Model updated');
    }


    /**
     * Build the TypeScript widget (frontend/) into assets/…/js/web/
     */
    protected function assets()
    {
        $frontend = $this->config['root'] . 'frontend/';
        if (!is_file($frontend . 'package.json')) {
            $this->modx->log(modX::LOG_LEVEL_WARN, 'frontend/package.json not found, skipping widget build');

            return;
        }

        $path = getenv('PATH') ?: '';
        $extra = dirname(MODX_BASE_PATH);
        if ($extra && strpos($path, $extra) === false) {
            putenv('PATH=' . $path . ':' . $extra);
        }

        $npm = trim((string) shell_exec('command -v npm 2>/dev/null'));
        if ($npm === '') {
            $this->modx->log(
                modX::LOG_LEVEL_WARN,
                'npm not found: widget not rebuilt. Run: cd frontend && npm install && npm run build'
            );

            return;
        }

        if (!is_dir($frontend . 'node_modules')) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Installing frontend npm dependencies…');
            $install = shell_exec('cd ' . escapeshellarg($frontend) . ' && npm install 2>&1');
            $this->modx->log(modX::LOG_LEVEL_INFO, trim((string) $install));
        }

        $this->modx->log(modX::LOG_LEVEL_INFO, 'Building frontend widget (vite)…');
        $build = shell_exec('cd ' . escapeshellarg($frontend) . ' && npm run build 2>&1');
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Widget build: ' . trim((string) $build));

        $js = $this->config['assets'] . 'js/web/yandexmapslocator.js';
        if (!is_file($js)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Widget build failed: missing ' . $js);
        }
    }

    /**
     *  Install package
     */
    private function install()
    {
        $signature = $this->builder->getSignature();
        $sig = explode('-', $signature);
        $versionSignature = explode('.', $sig[1]);

        /** @var modTransportPackage $package */
        $package = $this->modx->getObject(modTransportPackage::class, ['signature' => $signature]);
        if (!$package) {
            $package = $this->modx->newObject(modTransportPackage::class);
            $package->set('signature', $signature);
            $package->fromArray([
                'created' => date('Y-m-d h:i:s'),
                'updated' => null,
                'state' => 1,
                'workspace' => 1,
                'provider' => 0,
                'source' => $signature . '.transport.zip',
                'package_name' => $this->config['name'],
                'version_major' => $versionSignature[0],
                'version_minor' => !empty($versionSignature[1]) ? $versionSignature[1] : 0,
                'version_patch' => !empty($versionSignature[2]) ? $versionSignature[2] : 0,
            ]);
            if (!empty($sig[2])) {
                $r = preg_split('#([0-9]+)#', $sig[2], -1, PREG_SPLIT_DELIM_CAPTURE);
                if (is_array($r) && !empty($r)) {
                    $package->set('release', $r[0]);
                    $package->set('release_index', (isset($r[1]) ? $r[1] : '0'));
                } else {
                    $package->set('release', $sig[2]);
                }
            }
            $package->save();
        }
        if (isset($package->xpdo->packages['Revolution'])) {
            $package->xpdo->packages['MODX\Revolution\\'] = $package->xpdo->packages['Revolution'];
        }
        if ($package->install()) {
            $this->modx->runProcessor('System/ClearCache');
        }
    }

    /**
     * Settings whose values must survive package upgrade/reinstall.
     *
     * @return list<string> short names from settings.php (without namespace prefix)
     */
    private function preservedSettingNames(): array
    {
        return [
            'api_key',
            'api_token',
            'api_cors_origins',
            'api_resource_tvs',
            'api_allowed_parents',
        ];
    }

    /**
     * Add settings
     */
    private function settings()
    {
        /** @noinspection PhpIncludeInspection */
        $settings = include($this->config['elements'] . 'settings.php');
        if (!is_array($settings)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in System Settings');
            return;
        }
        $attributes = [
            xPDOTransport::UNIQUE_KEY => 'key',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['settings']),
            xPDOTransport::RELATED_OBJECTS => false,
        ];
        $preserveValueSettings = $this->preservedSettingNames();
        foreach ($settings as $name => $data) {
            $payload = array_merge([
                'key' => $this->config['name_lower'] . '_' . $name,
                'namespace' => $this->config['name_lower'],
            ], $data);
            $vehicleAttributes = $attributes;
            if (in_array($name, $preserveValueSettings, true)) {
                // Never overwrite user secrets / free-text API options on upgrade.
                $vehicleAttributes[xPDOTransport::UPDATE_OBJECT] = false;
            }
            // Empty default не кладём в transport — иначе update затирает value пустой строкой.
            if (array_key_exists('value', $payload) && (string) $payload['value'] === '') {
                unset($payload['value']);
            }
            /** @var modSystemSetting $setting */
            $setting = $this->modx->newObject(modSystemSetting::class);
            $setting->fromArray($payload, '', true, true);
            // Preserve secrets via UPDATE_OBJECT=false (+ settings resolver). Never set value=null:
            // modSystemSetting.value is NOT NULL and xPDO logs errors during packaging.
            $vehicle = $this->builder->createVehicle($setting, $vehicleAttributes);
            $this->builder->putVehicle($vehicle);
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($settings) . ' System Settings');
    }


    /**
     * Add menus
     */
    private function menus()
    {
        /** @noinspection PhpIncludeInspection */
        $menus = include($this->config['elements'] . 'menus.php');
        if (!is_array($menus)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Menus');

            return;
        }
        $attributes = [
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['menus']),
            xPDOTransport::UNIQUE_KEY => 'text',
            xPDOTransport::RELATED_OBJECTS => true,
        ];
        if (is_array($menus)) {
            foreach ($menus as $name => $data) {
                /** @var modMenu $menu */
                $menu = $this->modx->newObject(modMenu::class);
                $menu->fromArray(array_merge([
                    'text' => $name,
                    'parent' => 'components',
                    'namespace' => $this->config['name_lower'],
                    'icon' => '',
                    'menuindex' => 0,
                    'params' => '',
                    'handler' => '',
                ], $data), '', true, true);
                $vehicle = $this->builder->createVehicle($menu, $attributes);
                $this->builder->putVehicle($vehicle);
            }
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($menus) . ' Menus');
    }


    /**
     * Add Dashboard Widgets
     */
    private function widgets()
    {
        /** @noinspection PhpIncludeInspection */
        $widgets = include($this->config['elements'] . 'widgets.php');
        if (!is_array($widgets)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Dashboard Widgets');

            return;
        }
        $attributes = [
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['widgets']),
            xPDOTransport::UNIQUE_KEY => 'name',
        ];
        foreach ($widgets as $name => $data) {
            /** @var modDashboardWidget $widget */
            $widget = $this->modx->newObject(modDashboardWidget::class);
            $widget->fromArray(array_merge([
                'name' => $name,
                'namespace' => 'core',
                'lexicon' => 'core:dashboards',
            ], $data), '', true, true);
            $vehicle = $this->builder->createVehicle($widget, $attributes);
            $this->builder->putVehicle($vehicle);
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($widgets) . ' Dashboard Widgets');
    }


    /**
     * Add resources
     */
    private function resources()
    {
        /** @noinspection PhpIncludeInspection */
        $resources = include($this->config['elements'] . 'resources.php');
        if (!is_array($resources)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Resources');

            return;
        }
        $attributes = [
            xPDOTransport::UNIQUE_KEY => 'id',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['resources']),
            xPDOTransport::RELATED_OBJECTS => false,
        ];
        $objects = [];
        foreach ($resources as $context => $items) {
            $menuindex = 0;
            foreach ($items as $alias => $item) {
                if (!isset($item['id'])) {
                    $item['id'] = $this->_idx++;
                }
                $item['alias'] = $alias;
                $item['context_key'] = $context;
                $item['menuindex'] = $menuindex++;
                $objects = array_merge(
                    $objects,
                    $this->createResource($item, $alias)
                );
            }
        }

        /** @var modResource $resource */
        foreach ($objects as $resource) {
            $vehicle = $this->builder->createVehicle($resource, $attributes);
            $this->builder->putVehicle($vehicle);
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($objects) . ' Resources');
    }


    /**
     * Add plugins
     */
    private function plugins()
    {
        /** @noinspection PhpIncludeInspection */
        $plugins = include($this->config['elements'] . 'plugins.php');
        if (!is_array($plugins)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Plugins');

            return;
        }
        $this->category_attributes[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Plugins'] = [
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['plugins']),
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
                'PluginEvents' => [
                    xPDOTransport::PRESERVE_KEYS => true,
                    xPDOTransport::UPDATE_OBJECT => true,
                    xPDOTransport::UNIQUE_KEY => ['pluginid', 'event'],
                ],
            ],
        ];
        $objects = [];
        foreach ($plugins as $name => $data) {
            /** @var modPlugin $plugin */
            $plugin = $this->modx->newObject(modPlugin::class);
            $plugin->fromArray(array_merge([
                'name' => $name,
                'category' => 0,
                'description' => @$data['description'],
                'plugincode' => $this::getFileContent($this->config['core'] . 'elements/plugins/' . $data['file'] . '.php'),
                'static' => !empty($this->config['static']['plugins']),
                'source' => 1,
                'static_file' => 'core/components/' . $this->config['name_lower'] . '/elements/plugins/' . $data['file'] . '.php',
            ], $data), '', true, true);

            $events = [];
            if (!empty($data['events'])) {
                foreach ($data['events'] as $event_name => $event_data) {
                    /** @var modPluginEvent $event */
                    $event = $this->modx->newObject(modPluginEvent::class);
                    $event->fromArray(array_merge([
                        'event' => $event_name,
                        'priority' => 0,
                        'propertyset' => 0,
                    ], $event_data), '', true, true);
                    $events[] = $event;
                }
            }
            if (!empty($events)) {
                $plugin->addMany($events);
            }
            $objects[] = $plugin;
        }
        $this->category->addMany($objects);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($objects) . ' Plugins');
    }


    /**
     * Add snippets
     */
    private function snippets()
    {
        /** @noinspection PhpIncludeInspection */
        $snippets = include($this->config['elements'] . 'snippets.php');
        if (!is_array($snippets)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Snippets');
            return;
        }
        $this->category_attributes[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Snippets'] = [
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['snippets']),
            xPDOTransport::UNIQUE_KEY => 'name',
        ];
        $objects = [];
        foreach ($snippets as $name => $data) {
            /** @var modSnippet $snippet */
            $objects[$name] = $this->modx->newObject(modSnippet::class);
            $objects[$name]->fromArray(array_merge([
                'id' => 0,
                'name' => $name,
                'description' => @$data['description'],
                'snippet' => $this::getFileContent($this->config['core'] . 'elements/snippets/' . $data['file'] . '.php'),
                'static' => !empty($this->config['static']['snippets']),
                'source' => 1,
                'static_file' => 'core/components/' . $this->config['name_lower'] . '/elements/snippets/' . $data['file'] . '.php',
            ], $data), '', true, true);
            $properties = [];
            foreach (@$data['properties'] as $k => $v) {
                $properties[] = array_merge([
                    'name' => $k,
                    'desc' => $this->config['name_lower'] . '_prop_' . $k,
                    'lexicon' => $this->config['name_lower'] . ':properties',
                ], $v);
            }
            $objects[$name]->setProperties($properties);
        }
        $this->category->addMany($objects);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($objects) . ' Snippets');
    }


    /**
     * Add chunks
     */
    private function chunks()
    {
        /** @noinspection PhpIncludeInspection */
        $chunks = include($this->config['elements'] . 'chunks.php');
        if (!is_array($chunks)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Chunks');

            return;
        }
        $this->category_attributes[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Chunks'] = [
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['chunks']),
            xPDOTransport::UNIQUE_KEY => 'name',
        ];
        $objects = [];
        foreach ($chunks as $name => $data) {
            /** @var modChunk[] $objects */
            $objects[$name] = $this->modx->newObject(modChunk::class);
            $objects[$name]->fromArray(array_merge([
                'id' => 0,
                'name' => $name,
                'description' => @$data['description'],
                'snippet' => $this::getFileContent($this->config['core'] . 'elements/chunks/' . $data['file'] . '.tpl'),
                'static' => !empty($this->config['static']['chunks']),
                'source' => 1,
                'static_file' => 'core/components/' . $this->config['name_lower'] . '/elements/chunks/' . $data['file'] . '.tpl',
            ], $data), '', true, true);
            $objects[$name]->setProperties(@$data['properties']);
        }
        $this->category->addMany($objects);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($objects) . ' Chunks');
    }


    /**
     * Add templates
     */
    private function templates()
    {
        /** @noinspection PhpIncludeInspection */
        $templates = include($this->config['elements'] . 'templates.php');
        if (!is_array($templates)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Templates');

            return;
        }
        $this->category_attributes[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Templates'] = [
            xPDOTransport::UNIQUE_KEY => 'templatename',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['templates']),
            xPDOTransport::RELATED_OBJECTS => false,
        ];
        $objects = [];
        foreach ($templates as $name => $data) {
            /** @var modTemplate[] $objects */
            $objects[$name] = $this->modx->newObject(modTemplate::class);
            $objects[$name]->fromArray(array_merge([
                'templatename' => $name,
                'description' => $data['description'],
                'content' => $this::getFileContent($this->config['core'] . 'elements/templates/' . $data['file'] . '.tpl'),
                'static' => !empty($this->config['static']['templates']),
                'source' => 1,
                'static_file' => 'core/components/' . $this->config['name_lower'] . '/elements/templates/' . $data['file'] . '.tpl',
            ], $data), '', true, true);
        }
        $this->category->addMany($objects);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($objects) . ' Templates');
    }


    /**
     * Add access policy
     */
    private function policies()
    {
        /** @noinspection PhpIncludeInspection */
        $policies = include($this->config['elements'] . 'policies.php');
        if (!is_array($policies)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Access Policies');
            return;
        }
        $attributes = [
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UNIQUE_KEY => array('name'),
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['policies']),
        ];
        foreach ($policies as $name => $data) {
            if (isset($data['data'])) {
                $data['data'] = json_encode($data['data']);
            }
            /** @var $policy modAccessPolicy */
            $policy = $this->modx->newObject(modAccessPolicy::class);
            $policy->fromArray(array_merge(array(
                    'name' => $name,
                    'lexicon' => $this->config['name_lower'] . ':permissions',
                ), $data)
                , '', true, true);
            $vehicle = $this->builder->createVehicle($policy, $attributes);
            $this->builder->putVehicle($vehicle);
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($policies) . ' Access Policies');
    }


    /**
     * Add policy templates
     */
    private function policy_templates()
    {
        /** @noinspection PhpIncludeInspection */
        $policy_templates = include($this->config['elements'] . 'policy_templates.php');
        if (!is_array($policy_templates)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Policy Templates');
            return;
        }
        $attributes = [
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UNIQUE_KEY => array('name'),
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['policy_templates']),
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
                'Permissions' => array(
                    xPDOTransport::PRESERVE_KEYS => false,
                    xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['permission']),
                    xPDOTransport::UNIQUE_KEY => array('template', 'name'),
                ),
            ),
        ];
        foreach ($policy_templates as $name => $data) {
            $permissions = array();
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                foreach ($data['permissions'] as $name2 => $data2) {
                    /** @var $permission modAccessPermission */
                    $permission = $this->modx->newObject(modAccessPermission::class);
                    $permission->fromArray(array_merge(array(
                            'name' => $name2,
                            'description' => $name2,
                            'value' => true,
                        ), $data2)
                        , '', true, true);
                    $permissions[] = $permission;
                }
            }
            /** @var $permission modAccessPolicyTemplate */
            $permission = $this->modx->newObject(modAccessPolicyTemplate::class);
            $permission->fromArray(array_merge(array(
                    'name' => $name,
                    'lexicon' => $this->config['name_lower'] . ':permissions',
                ), $data)
                , '', true, true);
            if (!empty($permissions)) {
                $permission->addMany($permissions);
            }
            $vehicle = $this->builder->createVehicle($permission, $attributes);
            $this->builder->putVehicle($vehicle);
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($policy_templates) . ' Access Policy Templates');
    }

    /**
     * @param $filename
     *
     * @return string
     */
    private function getFileContent($filename)
    {
        if (file_exists($filename)) {
            $file = trim(file_get_contents($filename));

            return preg_match('#\<\?php(.*)#is', $file, $data)
                ? rtrim(rtrim(trim(@$data[1]), '?>'))
                : $file;
        }

        return '';
    }

    /**
     * @param array $data
     * @param string $uri
     * @param int $parent
     *
     * @return array
     */
    protected function createResource(array $data, $uri, $parent = 0)
    {
        $file = $data['context_key'] . '/' . $uri;
        /** @var modResource $resource */
        $resource = $this->modx->newObject(modResource::class);
        $resource->fromArray(array_merge([
            'parent' => $parent,
            'published' => true,
            'deleted' => false,
            'hidemenu' => false,
            'createdon' => time(),
            'template' => 1,
            'isfolder' => !empty($data['isfolder']) || !empty($data['resources']),
            'uri' => $uri,
            'uri_override' => false,
            'richtext' => false,
            'searchable' => true,
            'content' => $this::getFileContent($this->config['core'] . 'elements/resources/' . $file . '.tpl'),
        ], $data), '', true, true);

        if (!empty($data['groups'])) {
            foreach ($data['groups'] as $group) {
                $resource->joinGroup($group);
            }
        }
        $resources[] = $resource;

        if (!empty($data['resources'])) {
            $menuindex = 0;
            foreach ($data['resources'] as $alias => $item) {
                if (!isset($item['id'])) {
                    $item['id'] = $this->_idx++;
                }
                $item['alias'] = $alias;
                $item['context_key'] = $data['context_key'];
                $item['menuindex'] = $menuindex++;
                $resources = array_merge(
                    $resources,
                    $this->createResource($item, $uri . '/' . $alias, $data['id'])
                );
            }
        }

        return $resources;
    }
}

/** @var array $config */
if (!file_exists(dirname(__FILE__) . '/config.inc.php')) {
    exit('Could not load MODX config. Please specify correct MODX_CORE_PATH constant in config file!');
}
$config = require(dirname(__FILE__) . '/config.inc.php');
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$install = new YandexMapsLocatorPackage($modx, $config);
$builder = $install->process();

if (!empty($config['download'])) {
    $name = $builder->getSignature() . '.transport.zip';
    if ($content = file_get_contents(MODX_CORE_PATH . '/packages/' . $name)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $name);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($content));
        exit($content);
    }
}
