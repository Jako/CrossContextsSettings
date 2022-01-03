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
        $properties = $this->getProperties();
        $contexts = [];

        foreach ($this->contexts as $fk) {
            $value = trim($properties['value'][$fk]);
            if ($value === '') {
                continue;
            }
            $result = $this->modx->runProcessor('context/setting/create', [
                'fk' => $fk,
                'key' => $properties['key'],
                'name' => $properties['name'],
                'description' => $properties['description'],
                'namespace' => $properties['namespace'],
                'xtype' => $properties['xtype'],
                'area' => $properties['area'],
                'value' => $value,
            ]);
            if ($result->isError()) {
                $response = $result->getAllErrors();
                return $this->failure(isset($response[0]) ? $response[0] : '');
            }
            $contexts[$fk] = 1;
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
