<?php
/**
 * CrossContextsSettings
 *
 * Copyright 2014-2020 by goldsky <goldsky@virtudraft.com>
 * Copyright 2021 by Thomas Jakobi <thomas.jakobi@partout.info>
 *
 * @package crosscontextssettings
 * @subpackage build
 */

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
     * The version
     * @var string $version
     */
    public $version = '1.1.1';

    /**
     * The class options
     * @var array $options
     */
    public $options = array();

    /**
     * CrossContextsSettings constructor
     *
     * @param modX $modx A reference to the modX instance.
     * @param array $options An array of options. Optional.
     */
    public function __construct(modX $modx, $options = array())
    {
        $this->modx = &$modx;
        $this->namespace = $this->getOption('namespace', $options, $this->namespace);

        $corePath = $this->getOption('core_path', $options, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/' . $this->namespace . '/');
        $assetsPath = $this->getOption('assets_path', $options, $this->modx->getOption('assets_path', null, MODX_ASSETS_PATH) . 'components/' . $this->namespace . '/');
        $assetsUrl = $this->getOption('assets_url', $options, $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/' . $this->namespace . '/');

        // Load some default paths for easier management
        $this->options = array_merge(array(
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
            'connectorUrl' => $assetsUrl . 'connector.php'
        ), $options);

        // Add default options
        $this->options = array_merge($this->options, array(
            'debug' => (bool)$this->modx->getOption($this->namespace . '.debug', null, '0') == 1,
            'is_admin' => ($this->modx->user) ? $modx->hasPermission('settings') || $modx->hasPermission($this->namespace . '_settings') : false,
            'contexts' => $this->modx->getOption($this->namespace . '.contexts', null, '') ? explode(',', $this->modx->getOption($this->namespace . '.contexts', null, '')) : array(),
            'clear_cache' => (bool)$this->modx->getOption($this->namespace . '.clear_cache', null, '0') == 1
        ));

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
    public function getOption($key, $options = array(), $default = null)
    {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options != null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->options)) {
                $option = $this->options[$key];
            } elseif (array_key_exists("{$this->namespace}.{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}.{$key}");
            }
        }
        return $option;
    }
}
