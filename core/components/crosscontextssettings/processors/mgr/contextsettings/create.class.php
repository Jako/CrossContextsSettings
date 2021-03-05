<?php
/**
 * Create context setting processor for CrossContextsSettings
 *
 * @package crosscontextssettings
 * @subpackage processor
 */

class CrossContextsSettingsSettingsCreateProcessor extends modProcessor
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
        $contexts = $this->getProperty('contexts', false);
        if (empty($contexts)) {
            return $this->modx->lexicon('setting_err_nf');
        }
        $this->contexts = json_decode($contexts, true);
        foreach ($this->contexts as $fk) {
            $context = $this->modx->getContext($fk);
            if (empty($context)) {
                return $this->modx->lexicon('setting_err_nf');
            }
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

        foreach ($this->contexts as $fk) {
            $value = trim($props['value'][$fk]);
            if ($value === '') {
                continue;
            }
            $result = $this->modx->runProcessor('context/setting/create', array(
                'fk' => $fk,
                'key' => $props['key'],
                'name' => $props['name'],
                'description' => $props['description'],
                'namespace' => $props['namespace'],
                'xtype' => $props['xtype'],
                'area' => $props['area'],
                'value' => $value,
            ));
            if ($result->isError()) {
                $response = $result->getAllErrors();
                return $this->failure(isset($response[0]) ? $response[0] : '');
            }
            $contexts[$fk] = 1;
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

return 'CrossContextsSettingsSettingsCreateProcessor';
