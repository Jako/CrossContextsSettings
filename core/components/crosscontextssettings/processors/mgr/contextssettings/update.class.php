<?php
/**
 * Update contexts setting from grid
 *
 * @package crosscontextssettings
 * @subpackage processors
 */

use TreehillStudio\CrossContextsSettings\Processors\ObjectProcessor;

class CrossContextsSettingsSettingsSettingsUpdateProcessor extends ObjectProcessor
{
    public $classKey = 'modContextSetting';
    public $languageTopics = ['crosscontextssettings:default', 'setting'];
    public $permission = 'settings';
    public $objectType = 'setting';

    /**
     * Change the xtype, namespace and area of existing context settings too.
     * Disabled for the grid, because a grid row shows only one xtype,
     * namespace and area for the different contexts.
     * @var bool $updateMeta
     */
    protected $updateMeta = true;

    /**
     * {@inheritDoc}
     * @return bool|string
     */
    public function initialize()
    {
        $key = $this->getProperty('key', false);
        if (empty($key) || !is_string($key)) {
            return $this->modx->lexicon('setting_err_ns');
        }
        return parent::initialize();
    }

    /**
     * {@inheritDoc}
     * @return array|string
     */
    public function process()
    {
        // Use the MODX defaults for missing properties
        $properties = array_merge(['xtype' => 'textfield', 'namespace' => 'core', 'area' => ''], $this->getProperties());
        $contexts = [];

        foreach ($properties as $k => $v) {
            if (in_array($k, ['action', 'key', 'name', 'xtype', 'namespace', 'area', 'description', 'menu'])) {
                continue;
            }
            // Only change the setting in the contexts managed (and shown) by CrossContextsSettings
            if (!in_array((string)$k, $this->crosscontextssettings->getOption('contexts_keys'), true)) {
                continue;
            }
            /** @var modContextSetting $setting */
            $setting = $this->modx->getObject($this->classKey, [
                'key' => $properties['key'],
                'context_key' => $k,
            ]);
            $v = $this->normalizeSettingValue($v, $properties['xtype']);
            if ($v !== '') {
                if (!$setting) {
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
                        $this->refreshContexts($contexts);
                        return $this->failure($message);
                    }
                    $contexts[$k] = 1;
                    continue;
                }
                $fields = ['value' => $v];
                if ($this->updateMeta) {
                    $fields['xtype'] = $properties['xtype'];
                    $fields['namespace'] = $properties['namespace'];
                    $fields['area'] = $properties['area'];
                }
                // Skip saving unchanged settings
                $changed = false;
                foreach ($fields as $field => $value) {
                    if ((string)$setting->get($field) !== (string)$value) {
                        $changed = true;
                        break;
                    }
                }
                if (!$changed) {
                    continue;
                }
                $setting->fromArray($fields);
                if ($setting->save() === false) {
                    $message = $this->modx->lexicon('crosscontextssettings.contextsetting_err_save', ['key' => $properties['key'], 'context' => $k]);
                    $this->modx->log(xPDO::LOG_LEVEL_ERROR, $message, '', 'CrossContextsSettings');
                    $this->refreshContexts($contexts);
                    return $this->failure($message);
                }
                $contexts[$k] = 1;
            } else {
                if ($setting) {
                    if ($setting->remove() === false) {
                        $message = $this->modx->lexicon('setting_err_remove');
                        $this->modx->log(xPDO::LOG_LEVEL_ERROR, $message, '', 'CrossContextsSettings');
                        $this->refreshContexts($contexts);
                        return $this->failure($message);
                    }
                    $contexts[$k] = 1;
                }
            }
        }

        $this->refreshContexts($contexts);

        return $this->success();
    }
}

return 'CrossContextsSettingsSettingsSettingsUpdateProcessor';
