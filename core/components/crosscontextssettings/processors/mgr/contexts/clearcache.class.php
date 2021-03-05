<?php
/**
 * Clear cache processor for CrossContextsSettings
 *
 * @package crosscontextssettings
 * @subpackage processor
 */

class CrossContextsSettingsContextsClearCacheProcessor extends modProcessor
{
    public $objectType = 'crosscontextssettings.context';
    public $languageTopics = array('setting', 'namespace');
    public $permission = 'settings';

    /**
     * @return bool|string
     */
    public function initialize()
    {
        $ctxs = $this->getProperty('ctxs', false);
        if (empty($ctxs)) {
            return $this->modx->lexicon('crosscontextssettings.context_err_ns');
        }
        $check = false;
        foreach ($ctxs as $ctx => $val) {
            if ($val == '1') {
                $check = true;
                break;
            }
        }
        if (empty($check)) {
            return $this->modx->lexicon('crosscontextssettings.context_err_ns');
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function process()
    {
        $ctxs = $this->getProperty('ctxs', false);
        $contexts = array();
        foreach ($ctxs as $ctx => $val) {
            if ($val == '1') {
                $contexts[] = $ctx;
            }
        }
        if (!empty($contexts)) {
            if (!$this->modx->cacheManager->refresh(array(
                'auto_publish' => array('contexts' => array_diff($contexts, array('mgr'))),
                'system_settings' => array(),
                'context_settings' => array('contexts' => $contexts),
                'lexicon_topics' => array(),
                'resource' => array('contexts' => array_diff($contexts, array('mgr'))),
                'menu' => array(),
                'action_map' => array()
            ))) {
                return $this->failure();
            }
        }

        return $this->success();
    }
}

return 'CrossContextsSettingsContextsClearCacheProcessor';
