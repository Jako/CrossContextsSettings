<?php
/**
 * CrossContextsSettings
 *
 * Copyright 2014-2020 by goldsky <goldsky@virtudraft.com>
 * Copyright 2021-2024 by Thomas Jakobi <office@treehillstudio.com>
 *
 * @package crosscontextssettings
 * @subpackage classfile
 */

namespace TreehillStudio\CrossContextsSettings;

use modContext;
use modX;

/**
 * class CrossContextsSettings
 */
class CrossContextsSettings
{
    /**
     * A reference to the modX instance
     * @var modX $modx
     */
    public $modx;

    /**
     * The namespace
     * @var string $namespace
     */
    public $namespace = 'crosscontextssettings';

    /**
     * The package name
     * @var string $packageName
     */
    public $packageName = 'CrossContextsSettings';

    /**
     * The version
     * @var string $version
     */
    public $version = '1.2.7';

    /**
     * The class options
     * @var array $options
     */
    public $options = [];

    /**
     * CrossContextsSettings constructor
     *
     * @param modX $modx A reference to the modX instance.
     * @param array $options An array of options. Optional.
     */
    public function __construct(modX $modx, $options = [])
    {
        $this->modx =& $modx;
        $this->namespace = $this->getOption('namespace', $options, $this->namespace);

        $corePath = $this->getOption('core_path', $options, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/' . $this->namespace . '/');
        $assetsPath = $this->getOption('assets_path', $options, $this->modx->getOption('assets_path', null, MODX_ASSETS_PATH) . 'components/' . $this->namespace . '/');
        $assetsUrl = $this->getOption('assets_url', $options, $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/' . $this->namespace . '/');
        $modxversion = $this->modx->getVersionData();

        // Load some default paths for easier management
        $this->options = array_merge([
            'namespace' => $this->namespace,
            'version' => $this->version,
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'vendorPath' => $corePath . 'vendor/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'pagesPath' => $corePath . 'elements/pages/',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'pluginsPath' => $corePath . 'elements/plugins/',
            'controllersPath' => $corePath . 'controllers/',
            'processorsPath' => $corePath . 'processors/',
            'templatesPath' => $corePath . 'templates/',
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',
            'imagesUrl' => $assetsUrl . 'images/',
            'connectorUrl' => $assetsUrl . 'connector.php',
            'cronUrl' => $assetsUrl . 'cron.php'
        ], $options);

        // Add default options
        $this->options = array_merge($this->options, [
            'debug' => (bool)$this->getOption('debug', $options, false),
            'modxversion' => $modxversion['version'],
            'is_admin' => $this->modx->user && $this->modx->context && ($modx->hasPermission('settings') || $modx->hasPermission($this->namespace . '_settings')),
            'contexts' => $this->getContextList($this->modx->getOption($this->namespace . '.contexts')),
            'clear_cache' => (bool)$this->modx->getOption($this->namespace . '.clear_cache', null, '0') == 1
        ]);

        $contextsKeys = [];
        foreach ($this->getOption('contexts') as $context) {
            $contextsKeys[] = $context['key'];
        }
        $this->options['contexts_keys'] = $contextsKeys;

        $lexicon = $this->modx->getService('lexicon', 'modLexicon');
        $lexicon->load($this->namespace . ':default');
    }

    /**
     * Get a local configuration option or a namespaced system setting by key.
     *
     * @param string $key The option key to search for.
     * @param array $options An array of options that override local options.
     * @param mixed $default The default value returned if the option is not found locally or as a
     * namespaced system setting; by default this value is null.
     * @return mixed The option value or the default value specified.
     */
    public function getOption($key, $options = [], $default = null)
    {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options != null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->options)) {
                $option = $this->options[$key];
            } elseif (array_key_exists("$this->namespace.$key", $this->modx->config)) {
                $option = $this->modx->getOption("$this->namespace.$key");
            }
        }
        return $option;
    }

    /**
     * @param string $allowed
     * @return array
     */
    public function getContextList($allowed)
    {
        $allowed = ($allowed) ? array_map('trim', explode(',', $allowed)) : [];
        $data = [];

        $c = $this->modx->newQuery('modContext');
        $c->select($this->modx->getSelectColumns('modContext', 'modContext', '', ['key', 'name']));
        $c->where([
            'modContext.key:!=' => 'mgr',
        ]);
        if ($allowed) {
            $c->where([
                'modContext.key:IN' => $allowed
            ]);
        }
        $c->sortby('`rank`');

        /** @var modContext[] $contexts */
        $contexts = $this->modx->getIterator('modContext', $c);
        foreach ($contexts as $context) {
            $data[] = $context->toArray('', false, true);
        }
        return $data;
    }
}
