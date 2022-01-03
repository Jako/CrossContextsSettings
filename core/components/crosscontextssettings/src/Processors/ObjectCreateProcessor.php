<?php
/**
 * Abstract create processor
 *
 * @package crosscontextssettings
 * @subpackage processors
 */

namespace TreehillStudio\CrossContextsSettings\Processors;

use modObjectCreateProcessor;
use modX;
use TreehillStudio\CrossContextsSettings\CrossContextsSettings;

/**
 * Class ObjectCreateProcessor
 */
abstract class ObjectCreateProcessor extends modObjectCreateProcessor
{
    public $languageTopics = ['crosscontextssettings:default'];

    /** @var CrossContextsSettings $crosscontextssettings */
    public $crosscontextssettings;

    protected $required = [];

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
     * {@inheritDoc}
     * @return bool
     */
    public function beforeSave()
    {
        foreach ($this->required as $required) {
            $value = $this->getProperty($required);
            if (empty($value)) {
                $this->addFieldError($required, $this->modx->lexicon('field_required'));
            }
        }

        return parent::beforeSave();
    }
}
