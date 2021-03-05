<?php
/**
 * Get contexts list processor for CrossContextsSettings
 *
 * @package crosscontextssettings
 * @subpackage processor
 */

class CrossContextsSettingsContextsGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'modContext';
    public $languageTopics = array('crosscontextssettings:default');
    public $defaultSortField = 'key';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'crosscontextssettings.contexts';

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
     * @return array
     */
    public function getData(): array
    {
        $data = array();

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
        $c->where(array(
            $this->classKey . '.key:!=' => 'mgr'
        ));
        if ($contexts = $this->crosscontextssettings->getOption('contexts')) {
            $c->where(array($this->classKey . '.key:IN' => $contexts));
        }
        return $c;
    }

    /**
     * Make sure that 'web' context is at the first column if available
     *
     * @param array $list
     * @return array
     */
    public function afterIteration(array $list): array
    {
        $newList = array();
        $web = null;
        foreach ($list as $k => $v) {
            if ($v['key'] === 'web') {
                $web = $v;
                continue;
            }
            $newList[] = $v;
        }
        if ($web) {
            array_unshift($newList, $web);
        }

        return $newList;
    }
}

return 'CrossContextsSettingsContextsGetListProcessor';
