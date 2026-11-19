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
        // Use the MODX defaults for missing properties
        $properties = array_merge(['xtype' => 'textfield', 'namespace' => 'core', 'area' => ''], $this->getProperties());
        if (empty($properties['namespace'])) {
            $properties['namespace'] = 'core';
        }
        $contexts = [];

        $values = [];
        foreach ($properties as $k => $v) {
            if (in_array($k, ['action', 'key', 'name', 'xtype', 'namespace', 'area', 'description', 'menu'])) {
                continue;
            }
            $v = $this->normalizeSettingValue($v, $properties['xtype']);
            if ($v !== '' && in_array((string)$k, $this->crosscontextssettings->getOption('contexts_keys'), true)) {
                $values[(string)$k] = $v;
            }
        }
        if (empty($values)) {
            return $this->failure($this->modx->lexicon('crosscontextssettings.contextsetting_err_nv'));
        }

        // Don't create the setting partially, if it already exists in one of the contexts
        $existing = [];
        $c = $this->modx->newQuery('modContextSetting');
        $c->select($this->modx->getSelectColumns('modContextSetting', 'modContextSetting', '', ['context_key']));
        $c->where([
            'key' => $properties['key'],
            'context_key:IN' => array_keys($values),
        ]);
        if ($c->prepare() && $c->stmt->execute()) {
            $existing = $c->stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        if (!empty($existing)) {
            return $this->failure($this->modx->lexicon('crosscontextssettings.contextsetting_err_ae', [
                'key' => $properties['key'],
                'contexts' => implode(', ', $existing),
            ]));
        }

        foreach ($values as $k => $v) {
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
        }

        $this->refreshContexts($contexts);

        return $this->success();
    }
}

return 'CrossContextsSettingsSettingsCreateProcessor';
