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
    public $permission = 'settings';
    public $objectType = 'setting';

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
            $setting = $this->modx->getObject($this->classKey, [
                'key' => $properties['key'],
                'context_key' => $k,
            ]);
            $v = trim($v);
            if ($properties['xtype'] === 'combo-boolean' && empty($v)) {
                $v = 0;
            }
            if ($v !== '') {
                if (!$setting) {
                    if (isset($properties[$k])) {
                        /** @var modContextSetting $setting */
                        $setting = $this->modx->newObject($this->classKey);
                        $setting->fromArray([
                            'context_key' => $k,
                            'key' => $properties['key'],
                            'value' => $v,
                            'xtype' => $properties['xtype'],
                            'namespace' => $properties['namespace'],
                            'area' => $properties['area'],
                        ]);
                        if ($setting->save() === false) {
                            $message = $this->modx->lexicon('crosscontextssettings.contextsetting_err_save', ['key' => $properties['key'], 'context' => $k]);
                            $this->modx->log(modX::LOG_LEVEL_ERROR, $message, '', 'CrossContextsSettings');
                            continue;
                        }
                        $contexts[$k] = 1;
                    }
                    continue;
                }
                // Skip saving same value
                if ($setting->get('value') == $v) {
                    continue;
                }
                $setting->set('value', $v);
                if ($setting->save() === false) {
                    $message = $this->modx->lexicon('crosscontextssettings.contextsetting_err_save', ['key' => $properties['key'], 'context' => $k]);
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
            $this->modx->runProcessor('mgr/contexts/clearcache', [
                'ctxs' => $contexts,
            ], [
                'processors_path' => $this->crosscontextssettings->getOption('processorsPath')
            ]);
        }

        return $this->success();
    }
}

return 'CrossContextsSettingsSettingsSettingsUpdateProcessor';
