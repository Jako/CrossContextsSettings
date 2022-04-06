<?php
/**
 * Create contexts setting
 *
 * @package crosscontextssettings
 * @subpackage processors
 */

use TreehillStudio\CrossContextsSettings\Processors\ObjectProcessor;

class CrossContextsSettingsSettingsCreateProcessor extends ObjectProcessor
{
    public $languageTopics = ['crosscontextssettings:default', 'setting'];
    public $permission = 'settings';
    public $objectType = 'setting';
    public $primaryKeyField = 'key';

    protected $contexts = [];

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
     * @return array|string
     */
    public function process()
    {
        $properties = $this->getProperties();
        $contexts = [];

        foreach ($properties as $k => $v) {
            if (in_array($k, ['action', 'key', 'name', 'xtype', 'namespace', 'area', 'description', 'menu'])) {
                continue;
            }
            $v = trim($v);
            if ($properties['xtype'] === 'combo-boolean' && empty($v)) {
                $v = 0;
            }
            if ($v !== '' && $this->modx->getContext($k)) {
                $result = $this->modx->runProcessor('context/setting/create', [
                    'fk' => $k,
                    'key' => $properties['key'],
                    'value' => $v,
                    'xtype' => $properties['xtype'],
                    'namespace' => $properties['namespace'],
                    'area' => $properties['area']
                ]);
                if ($result->isError()) {
                    $response = $result->getAllErrors();
                    $message = isset($response[0]) ? $response[0] : $this->modx->lexicon('setting_err_save');
                    $this->modx->log(xPDO::LOG_LEVEL_ERROR, $message, '', 'CrossContextsSettings');
                    return $this->failure($message);
                }
                $contexts[$k] = 1;
            }
        }

        if ($this->crosscontextssettings->getOption('clear_cache')) {
            $this->modx->runProcessor('mgr/contexts/clearcache', [
                'ctxs' => $contexts,
            ], [
                'processors_path' => $this->crosscontextssettings->getOption('processorsPath')
            ]);
        }

        return $this->success();
    }
}

return 'CrossContextsSettingsSettingsCreateProcessor';
