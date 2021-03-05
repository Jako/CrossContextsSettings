<?php
/**
 * Remove context setting processor for CrossContextsSettings
 *
 * @package crosscontextssettings
 * @subpackage processor
 */

class CrossContextsSettingsSettingsRemoveProcessor extends modProcessor
{
    public $languageTopics = array('setting', 'namespace');
    public $permission = 'settings';
    public $objectType = 'setting';
    public $primaryKeyField = 'key';

    protected $contexts = array();
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
        $primaryKey = $this->getProperty($this->primaryKeyField, false);
        if (empty($primaryKey)) {
            return $this->modx->lexicon('setting_err_ns');
        }
        return true;
    }

    /**
     * {@inheritDoc}
     * @return mixed
     */
    public function process()
    {
        $props = $this->getProperties();
        $contexts = array();

        $contextSettings = $this->modx->getCollection('modContextSetting', array(
            'key' => $this->getProperty($this->primaryKeyField)
        ));
        if (!$contextSettings) {
            return $this->failure($this->modx->lexicon('setting_err_nf'));
        }
        foreach ($contextSettings as $setting) {
            $result = $this->modx->runProcessor('context/setting/remove', array(
                'context_key' => $setting->get('context_key'),
                'key' => $props['key'],
            ));
            if ($result->isError()) {
                $response = $result->getAllErrors();
                return $this->failure(isset($response[0]) ? $response[0] : '');
            }
            $contexts[$setting->get('context_key')] = 1;
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

return 'CrossContextsSettingsSettingsRemoveProcessor';
