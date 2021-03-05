<?php
/**
 * Update contexts settings from grid for CrossContextsSettings
 *
 * @package crosscontextssettings
 * @subpackage processor
 */

class SettingUpdateFromGridProcessor extends modObjectProcessor
{
    public $classKey = 'modContextSetting';
    public $languageTopics = array('crosscontextssettings:default');
    public $objectType = 'crosscontextssettings.settingsupdatefromgrid';

    /** @var CrossContextsSettings $crosscontextssettings */
    protected $crosscontextssettings;

    /**
     * {@inheritDoc}
     * @param modX $modx A reference to the modX instance
     * @param array $properties An array of properties
     */
    function __construct(modX &$modx, array $properties = [])
    {
        parent::__construct($modx, $properties);

        $corePath = $this->modx->getOption('crosscontextssettings.core_path', null, $this->modx->getOption('core_path') . 'components/crosscontextssettings/');
        $this->crosscontextssettings =& $this->modx->getService('crosscontextssettings', 'CrossContextsSettings', $corePath . 'model/crosscontextssettings/');
    }

    /**
     * {@inheritDoc}
     * @return bool|string
     */
    public function initialize()
    {
        $data = $this->getProperty('data');
        if (empty($data)) {
            return $this->modx->lexicon('invalid_data');
        }
        $data = $this->modx->fromJSON($data);
        if (empty($data)) {
            return $this->modx->lexicon('invalid_data');
        }
        $this->setProperties($data);
        $this->unsetProperty('data');

        return parent::initialize();
    }

    /**
     * {@inheritDoc}
     * @return mixed
     */
    public function process()
    {
        $props = $this->getProperties();

        $contexts = array();

        foreach ($props as $k => $v) {
            if (in_array($k, array('key', 'xtype', 'namespace', 'area', 'menu'))) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, __LINE__ . ': [CCS] ', '', 'CrossContextsSettings');
                continue;
            }
            $setting = $this->modx->getObject($this->classKey, array(
                'key' => $props['key'],
                'context_key' => $k,
            ));
            $v = trim($v);
            if ($props['xtype'] === 'combo-boolean' && empty($v)) {
                $v = 0;
            }
            if ($v !== '') {
                if (!$setting) {
                    if (isset($props[$k])) {
                        $setting = $this->modx->newObject($this->classKey);
                        $setting->set('key', $props['key']);
                        $setting->set('context_key', $k);
                        $setting->set('value', $v);
                        $setting->set('xtype', $props['xtype']);
                        if ($setting->save() === false) {
                            $message = $this->modx->lexicon('crosscontextssettings.contextsetting_err_save', array('key' => $props['key'], 'context' => $k));
                            $this->modx->log(modX::LOG_LEVEL_ERROR, $message, '', 'CrossContextsSettings');
                            continue;
                        }
                        $contexts[$k] = 1;
                    }
                    continue;
                }
                if ($setting->get('value') == $v) { // Skip saving same value
                    continue;
                }
                $setting->set('value', $v);
                if ($setting->save() === false) {
                    $message = $this->modx->lexicon('crosscontextssettings.contextsetting_err_save', array('key' => $props['key'], 'context' => $k));
                    $this->modx->log(modX::LOG_LEVEL_ERROR, $message, '', 'CrossContextsSettings');
                    return $this->failure($message);
                }
                $contexts[$k] = 1;
            } else {
                if ($setting) {
                    $setting->remove();
                }
            }
        }

        if ($this->crosscontextssettings->getOption('clear_cache')) {
            $this->modx->runProcessor('mgr/contexts/clearcache', array(
                'ctxs' => $contexts,
            ), array(
                'processors_path' => $this->crosscontextssettings->getOption('processorsPath')
            ));
        }

        return $this->success();
    }
}

return 'SettingUpdateFromGridProcessor';