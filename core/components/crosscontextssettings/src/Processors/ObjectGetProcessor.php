<?php
/**
 * Abstract get list processor
 *
 * @package crosscontextssettings
 * @subpackage processors
 */

namespace TreehillStudio\CrossContextsSettings\Processors;

use modObjectGetProcessor;
use modX;
use TreehillStudio\CrossContextsSettings\CrossContextsSettings;

/**
 * Class ObjectGetListProcessor
 */
abstract class ObjectGetProcessor extends modObjectGetProcessor
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
}
