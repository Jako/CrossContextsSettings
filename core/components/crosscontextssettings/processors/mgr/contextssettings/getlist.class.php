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
    protected $values = [];
    protected $xtypes = [];

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
        $c->select([
            'context_key' => 'MIN(' . $this->classKey . '.context_key)',
            'namespace' => 'MIN(' . $this->classKey . '.namespace)',
            'area' => 'MIN(' . $this->classKey . '.area)',
        ]);
        $c->where([
            $this->classKey . '.context_key:!=' => 'mgr'
        ]);
        // Only list the settings of the contexts managed (and shown) by CrossContextsSettings
        $c->where([
            $this->classKey . '.context_key:IN' => $this->crosscontextssettings->getOption('contexts_keys') ?: ['']
        ]);
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
     * Count the rows of the grouped query like xPDO 3 does. xPDO 2 counts a
     * grouped query by selecting the primary key (context_key, key), which
     * fails with ONLY_FULL_GROUP_BY enabled, since the query is grouped by key.
     *
     * @param xPDOQuery $c
     * @return int
     */
    protected function getTotal(xPDOQuery $c)
    {
        $total = 0;
        $cq = clone $c;
        $cq->query['columns'] = [];
        $cq->select('1 AS _c');
        if ($cq->prepare()) {
            $stmt = $this->modx->query('SELECT COUNT(*) FROM (' . $cq->toSQL() . ') cq');
            if ($stmt) {
                $total = (int)$stmt->fetchColumn();
            }
        }
        return $total;
    }

    /**
     * Sort namespace/area by the aggregated aliases, since the query is grouped by key
     * {@inheritDoc}
     * @return array
     */
    public function getData()
    {
        $data = [];
        $limit = intval($this->getProperty('limit'));
        $start = intval($this->getProperty('start'));

        $c = $this->modx->newQuery($this->classKey);
        $c = $this->prepareQueryBeforeCount($c);
        $data['total'] = $this->getTotal($c);
        $c = $this->prepareQueryAfterCount($c);

        $sort = $this->getProperty('sort');
        $dir = strtoupper($this->getProperty('dir')) === 'DESC' ? 'DESC' : 'ASC';
        if (in_array($sort, ['namespace', 'area'])) {
            $c->sortby($this->modx->escape($sort), $dir);
        } else {
            $c->sortby($this->modx->escape($this->classKey) . '.' . $this->modx->escape('key'), $dir);
        }
        if ($limit > 0) {
            $c->limit($limit, $start);
        }

        $data['results'] = $this->modx->getCollection($this->classKey, $c);
        $this->loadContextValues($data['results']);
        return $data;
    }

    /**
     * Load the values and xtypes of all settings in the current page with one query
     *
     * @param xPDOObject[] $results
     */
    protected function loadContextValues($results)
    {
        $this->values = [];
        $this->xtypes = [];

        $keys = [];
        foreach ($results as $result) {
            $keys[] = $result->get('key');
        }
        if (empty($keys)) {
            return;
        }

        $c = $this->modx->newQuery($this->classKey);
        $c->select($this->modx->getSelectColumns($this->classKey, $this->classKey, '', ['key', 'context_key', 'value', 'xtype']));
        $c->where([
            $this->classKey . '.key:IN' => $keys,
            $this->classKey . '.context_key:IN' => $this->crosscontextssettings->getOption('contexts_keys') ?: [''],
        ]);
        if ($c->prepare() && $c->stmt->execute()) {
            while ($row = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->values[$row['key']][$row['context_key']] = $row;
                if (empty($this->xtypes[$row['key']]) && $row['xtype'] !== '') {
                    $this->xtypes[$row['key']] = $row['xtype'];
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     * @param xPDOObject $object
     * @return array
     */
    public function prepareRow(xPDOObject $object)
    {
        $objectArray = $object->toArray();
        $key = $objectArray['key'];
        $values = $this->values[$key] ?? [];

        $output = [];
        if (!empty($this->columns) && is_array($this->columns)) {
            $xtype = '';
            foreach ($this->columns as $column) {
                if (in_array($column, ['key', 'namespace', 'area'])) {
                    $output[$column] = $objectArray[$column];
                } elseif ($column !== 'xtype') {
                    if (isset($values[$column])) {
                        $output[$column] = $values[$column]['value'];
                        $xtype = (string)$values[$column]['xtype'];
                    } else {
                        $output[$column] = '';
                    }
                }
            }
            // Use the xtype of the last context setting found, otherwise any xtype set for this key
            $output['xtype'] = ($xtype !== '') ? $xtype : ($this->xtypes[$key] ?? '');
        }

        return $output;
    }
}

return 'CrossContextsSettingsSettingsGetListProcessor';
