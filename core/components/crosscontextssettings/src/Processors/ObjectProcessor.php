<?php
/**
 * Abstract object processor
 *
 * @package crosscontextssettings
 * @subpackage processors
 */

namespace TreehillStudio\CrossContextsSettings\Processors;

use modObjectProcessor;
use modX;
use TreehillStudio\CrossContextsSettings\CrossContextsSettings;

/**
 * Class ObjectProcessor
 */
abstract class ObjectProcessor extends modObjectProcessor
{
    public $languageTopics = ['crosscontextssettings:default'];

    /** @var CrossContextsSettings $crosscontextssettings */
    public $crosscontextssettings;

    /**
     * {@inheritDoc}
     * @param modX $modx A reference to the modX instance
     * @param array $properties An array of properties
     */
    public function __construct(modX &$modx, array $properties = [])
    {
        parent::__construct($modx, $properties);

        $corePath = $this->modx->getOption('crosscontextssettings.core_path', null, $this->modx->getOption('core_path') . 'components/crosscontextssettings/');
        $this->crosscontextssettings = $this->modx->getService('crosscontextssettings', 'CrossContextsSettings', $corePath . 'model/crosscontextssettings/');
    }

    abstract public function process();

    /**
     * Get a boolean property.
     * @param string $k
     * @param mixed $default
     * @return bool
     */
    public function getBooleanProperty($k, $default = null)
    {
        return ($this->getProperty($k, $default) === 'true' || $this->getProperty($k, $default) === true || $this->getProperty($k, $default) === '1' || $this->getProperty($k, $default) === 1);
    }

    /**
     * Normalize a submitted setting value. An empty value stays empty (it
     * means "no setting in this context"), boolean values are normalized to
     * '1' or '0'.
     *
     * @param mixed $value
     * @param string $xtype
     * @return string
     */
    public function normalizeSettingValue($value, $xtype)
    {
        if ($value === null || is_array($value)) {
            return '';
        }
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }
        $value = trim((string)$value);
        if ($value !== '' && in_array($xtype, ['combo-boolean', 'modx-combo-boolean'])) {
            $value = in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true) ? '1' : '0';
        }
        return $value;
    }

    /**
     * Clear the cache of the changed contexts and invoke OnSiteRefresh. This
     * is called after a failure too, since the contexts changed before the
     * failure need a fresh cache as well.
     *
     * @param array $contexts The changed contexts as key => 1
     */
    public function refreshContexts(array $contexts)
    {
        if (empty($contexts)) {
            return;
        }
        if ($this->crosscontextssettings->getOption('clear_cache')) {
            $this->modx->runProcessor('mgr/contexts/clearcache', [
                'ctxs' => $contexts,
            ], [
                'processors_path' => $this->crosscontextssettings->getOption('processorsPath')
            ]);
        }
        $this->modx->invokeEvent('OnSiteRefresh');
    }
}
