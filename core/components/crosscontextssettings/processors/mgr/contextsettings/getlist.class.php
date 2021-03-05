<?php
/**
 * Get contexts settings list processor for CrossContextsSettings
 *
 * @package crosscontextssettings
 * @subpackage processor
 */

class CrossContextsSettingsSettingsGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'modContextSetting';
    public $languageTopics = array('crosscontextssettings:default');
    public $defaultSortField = 'key';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'crosscontextssettings.settings';

    protected $columns = array();
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
        $columns = $this->getProperty('columns');
        if ($columns) {
            $columns = array_map('trim', @explode(',', $columns));
            foreach ($columns as $k => $v) {
                if (empty($v)) {
                    unset($columns[$k]);
                }
            }
            $count = count($columns);
            if ($count < 3) {
                return $this->modx->lexicon('crosscontextssettings.context_err_ns');
            }
            $this->columns = $columns;
        }

        return parent::initialize();
    }

    /**
     * {@inheritDoc}
     * @return array
     */
    public function getData(): array
    {
        $data = array();
        $limit = intval($this->getProperty('limit'));
        $start = intval($this->getProperty('start'));

        /* query for chunks */
        $c = $this->modx->newQuery($this->classKey);
        $c = $this->prepareQueryBeforeCount($c);
        $data['total'] = $this->modx->getCount($this->classKey, $c);
        $c = $this->prepareQueryAfterCount($c);
        $sortClassKey = $this->getSortClassKey();
        $sortKey = $this->modx->getSelectColumns($sortClassKey, $this->getProperty('sortAlias', $sortClassKey), '', array($this->getProperty('sort')));
        if (empty($sortKey)) {
            $sortKey = $this->getProperty('sort');
        }
        $c->sortby($sortKey, $this->getProperty('dir'));
        if ($limit > 0) {
            $c->limit($limit, $start);
        }

        $data['results'] = $this->modx->getCollection($this->classKey, $c);
        return $data;
    }

    /**
     * {@inheritDoc}
     * @param xPDOQuery $c
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c): xPDOQuery
    {
        $c->select($this->modx->getSelectColumns($this->classKey, $this->classKey, '', array('key')));
        $c->select('MIN('.$this->classKey.'.context_key) as context_key');
        $c->where(array(
            $this->classKey . '.context_key:!=' => 'mgr'
        ));
        if ($contexts = $this->crosscontextssettings->getOption('contexts')) {
            $c->where(array($this->classKey . '.context_key:IN' => $contexts));
        }
        $namespace = $this->getProperty('namespace');
        if ($namespace && $namespace != 'core') {
            $c->where(array(
                $this->classKey . '.namespace:=' => $namespace
            ));
        }
        $area = $this->getProperty('area');
        if ($area) {
            $c->where(array(
                $this->classKey . '.area:=' => $area
            ));
        }
        $key = $this->getProperty('key');
        if ($key) {
            $c->where(array(
                $this->classKey . '.key:LIKE' => '%' . $key . '%'
            ));
        }
        $c->groupby($this->classKey . '.key', 'asc');

        return $c;
    }

    /**
     * {@inheritDoc}
     * @param xPDOObject $object
     * @return array
     */
    public function prepareRow(xPDOObject $object): array
    {
        $objectArray = $object->toArray();

        $output = array();
        if (!empty($this->columns) && is_array($this->columns)) {
            foreach ($this->columns as $column) {
                if ($column === 'key' || $column === 'namespace' || $column === 'area') {
                    $output[$column] = $objectArray[$column];
                } else {
                    $setting = $this->modx->getObject($this->classKey, array(
                        'context_key' => $column,
                        'key' => $objectArray['key'],
                    ));
                    if (!isset($output['xtype'])) {
                        $output['xtype'] = '';
                    }
                    if ($setting) {
                        $output[$column] = $setting->get('value');
                        $output['xtype'] = $setting->get('xtype');
                    } else {
                        $output[$column] = '';
                        if (empty($output['xtype'])) {
                            $check = $this->modx->getObject($this->classKey, array(
                                'key' => $objectArray['key'],
                                'xtype:!=' => '',
                            ));
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
