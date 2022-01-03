<?php
/**
 * Home controller class for CrossContextsSettings
 *
 * @package crosscontextssettings
 * @subpackage controller
 */

/**
 * Class CrossContextsSettingsHomeManagerController
 */
class CrossContextsSettingsHomeManagerController extends modExtraManagerController
{
    /** @var CrossContextsSettings $crosscontextssettings */
    public $crosscontextssettings;

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        $path = $this->modx->getOption('crosscontextssettings.core_path', null, $this->modx->getOption('core_path') . 'components/crosscontextssettings/');
        $this->crosscontextssettings = $this->modx->getService('crosscontextssettings', 'CrossContextsSettings', $path . 'model/crosscontextssettings/', [
            'core_path' => $path
        ]);

        parent::initialize();
    }

    /**
     * {@inheritDoc}
     */
    public function loadCustomCssJs()
    {
        $assetsUrl = $this->crosscontextssettings->getOption('assetsUrl');
        $jsUrl = $this->crosscontextssettings->getOption('jsUrl') . 'mgr/';
        $jsSourceUrl = $assetsUrl . '../../../source/js/mgr/';
        $cssUrl = $this->crosscontextssettings->getOption('cssUrl') . 'mgr/';
        $cssSourceUrl = $assetsUrl . '../../../source/css/mgr/';

        if ($this->crosscontextssettings->getOption('debug') && ($assetsUrl != MODX_ASSETS_URL . 'components/crosscontextssettings/')) {
            $this->addCss($jsSourceUrl . 'ux/LockingGridView/LockingGridView.css?v=v' . $this->crosscontextssettings->version);
            $this->addCss($cssSourceUrl . 'crosscontextssettings.css?v=v' . $this->crosscontextssettings->version);
            $this->addJavascript($jsSourceUrl . 'crosscontextssettings.js?v=v' . $this->crosscontextssettings->version);
            $this->addJavascript($jsSourceUrl . 'widgets/contextssettings.grid.js?v=v' . $this->crosscontextssettings->version);
            $this->addJavascript($jsSourceUrl . 'widgets/clearcache.panel.js?v=v' . $this->crosscontextssettings->version);
            $this->addJavascript($jsSourceUrl . 'widgets/home.panel.js?v=v' . $this->crosscontextssettings->version);
            $this->addJavascript(MODX_MANAGER_URL . 'assets/modext/widgets/core/modx.grid.settings.js?v=v' . $this->crosscontextssettings->version);
            $this->addJavascript($jsSourceUrl . 'ux/LockingGridView/LockingGridView.js?v=v' . $this->crosscontextssettings->version);
            $this->addJavascript($jsSourceUrl . 'widgets/settings.panel.js?v=v' . $this->crosscontextssettings->version);
            $this->addLastJavascript($jsSourceUrl . 'sections/home.js?v=v' . $this->crosscontextssettings->version);
        } else {
            $this->addCss($cssUrl . 'crosscontextssettings.min.css?v=v' . $this->crosscontextssettings->version);
            $this->addJavascript(MODX_MANAGER_URL . 'assets/modext/widgets/core/modx.grid.settings.js');
            $this->addLastJavascript($jsUrl . 'crosscontextssettings.min.js?v=v' . $this->crosscontextssettings->version);
        }
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            CrossContextsSettings.config = ' . json_encode($this->crosscontextssettings->options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . ';
            MODx.load({xtype: "crosscontextssettings-page-home"});
       });
        </script>');
    }

    /**
     * {@inheritDoc}
     * @return string[]
     */
    public function getLanguageTopics()
    {
        return ['core:setting', 'crosscontextssettings:default'];
    }

    /**
     * {@inheritDoc}
     * @param array $scriptProperties
     */
    public function process(array $scriptProperties = [])
    {
    }

    /**
     * {@inheritDoc}
     * @return string|null
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('crosscontextssettings');
    }

    /**
     * {@inheritDoc}
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->crosscontextssettings->getOption('templatesPath') . 'home.tpl';
    }
}
