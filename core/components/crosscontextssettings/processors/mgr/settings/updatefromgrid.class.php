<?php
/**
 * Update a system setting
 *
 * @package crosscontextssettings
 * @subpackage processors
 */

require_once dirname(__FILE__) . '/update.class.php';

class CrossContextsSettingsSystemSettingsUpdateFromGridProcessor extends CrossContextsSettingsSystemSettingsUpdateProcessor
{
    /**
     * {@inheritDoc}
     * @return bool|string|null
     */
    public function initialize() {
        $data = $this->getProperty('data');
        if (empty($data)) return $this->modx->lexicon('invalid_data');
        $properties = json_decode($data, true);
        $this->setProperties($properties);
        $this->unsetProperty('data');

        return parent::initialize();
    }
}

return 'CrossContextsSettingsSystemSettingsUpdateFromGridProcessor';
