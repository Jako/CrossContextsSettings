<?php
/**
 * Get list contexts settings
 *
 * @package crosscontextssettings
 * @subpackage processors
 */

use TreehillStudio\CrossContextsSettings\Processors\ObjectGetListProcessor;

class CrossContextsSettingsSettingsGetListProcessor extends ObjectGetListProcessor
{
    public $classKey = 'modContextSetting';
    public $defaultSortField = 'key';
    public $defaultSortDirection = 'ASC';
    public $permission = 'settings';
    public $objectType = 'setting';

    protected $columns = [];

    /**
     * {@inheritDoc}
     * @return bool|string
     */
    public function initialize()
    {
        $columns = $this->getProperty('columns');
        if ($columns) {
            $columns = array_map('trim', @explode(',', $columns));
            foreach ($columns as $k => $v) {
                if (empty($v)) {
                    unset($columns[$k]);
                }
            }
            $count = count($columns);
            if ($count <= 4) {
                return $this->modx->lexicon('crosscontextssettings.context_err_ns');
            }
            $this->columns = $columns;
        }

        return parent::initialize();
    }

    /**
     * {@inheritDoc}
     * @param xPDOQuery $c
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->select($this->modx->getSelectColumns($this->classKey, $this->classKey, '', ['key']));
        $c->select(['context_key' => 'MIN(' . $this->classKey . '.context_key)']);
        $c->where([
            $this->classKey . '.context_key:!=' => 'mgr'
        ]);
        if ($contextsKeys = $this->crosscontextssettings->getOption('contexts_keys')) {
            $c->where([$this->classKey . '.context_key:IN' => $contextsKeys]);
        }
        $namespace = $this->getProperty('namespace');
        if ($namespace) {
            $c->where([
                $this->classKey . '.namespace:=' => $namespace
            ]);
        }
        $area = $this->getProperty('area');
        if ($area) {
            $c->where([
                $this->classKey . '.area:=' => $area
            ]);
        }
        $key = $this->getProperty('key');
        if ($key) {
            $c->where([
                $this->classKey . '.key:LIKE' => '%' . $key . '%'
            ]);
        }
        $c->groupby($this->classKey . '.key');

        return $c;
    }

    /**
     * {@inheritDoc}
     * @param xPDOObject $object
     * @return array
     */
    public function prepareRow(xPDOObject $object)
    {
        $objectArray = $object->toArray();

        $output = [];
        if (!empty($this->columns) && is_array($this->columns)) {
            foreach ($this->columns as $column) {
                if ($column === 'key' || $column === 'namespace' || $column === 'area') {
                    $output[$column] = $objectArray[$column];
                } else {
                    $setting = $this->modx->getObject($this->classKey, [
                        'context_key' => $column,
                        'key' => $objectArray['key'],
                    ]);
                    if (!isset($output['xtype'])) {
                        $output['xtype'] = '';
                    }
                    if ($setting) {
                        $output[$column] = $setting->get('value');
                        $output['xtype'] = $setting->get('xtype');
                    } else {
                        $output[$column] = '';
                        if (empty($output['xtype'])) {
                            $check = $this->modx->getObject($this->classKey, [
                                'key' => $objectArray['key'],
                                'xtype:!=' => '',
                            ]);
                            if ($check) {
                                $output['xtype'] = $check->get('xtype');
                            }
                        }
                    }
                }
            }
        }

        return $output;
    }
}

return 'CrossContextsSettingsSettingsGetListProcessor';
