<?php
/**
 * Remove contexts setting
 *
 * @package crosscontextssettings
 * @subpackage processors
 */

use TreehillStudio\CrossContextsSettings\Processors\ObjectProcessor;

class CrossContextsSettingsSettingsRemoveProcessor extends ObjectProcessor
{
    public $languageTopics = ['crosscontextssettings:default', 'setting'];
    public $permission = 'settings';
    public $objectType = 'setting';
    public $primaryKeyField = 'key';

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
        $props = $this->getProperties();
        $contexts = [];

        $contextSettings = $this->modx->getCollection('modContextSetting', [
            'key' => $this->getProperty($this->primaryKeyField)
        ]);
        if (!$contextSettings) {
            return $this->failure($this->modx->lexicon('setting_err_nf'));
        }
        foreach ($contextSettings as $setting) {
            $result = $this->modx->runProcessor('context/setting/remove', [
                'context_key' => $setting->get('context_key'),
                'key' => $props['key'],
            ]);
            if ($result->isError()) {
                $response = $result->getAllErrors();
                return $this->failure($response[0] ?? '');
            }
            $contexts[$setting->get('context_key')] = 1;
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

return 'CrossContextsSettingsSettingsRemoveProcessor';
