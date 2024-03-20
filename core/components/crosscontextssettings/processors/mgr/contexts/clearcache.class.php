<?php
/**
 * Clear cache
 *
 * @package crosscontextssettings
 * @subpackage processors
 */

use TreehillStudio\CrossContextsSettings\Processors\ObjectProcessor;

class CrossContextsSettingsContextsClearCacheProcessor extends ObjectProcessor
{
    public $permission = 'settings';
    public $objectType = 'context';

    /**
     * {@inheritDoc}
     * @return bool|string
     */
    public function initialize()
    {
        $ctxs = $this->getProperty('ctxs', false);
        if (empty($ctxs)) {
            return $this->modx->lexicon('crosscontextssettings.context_err_ns');
        }
        $check = false;
        foreach ($ctxs as $val) {
            $check = ($val == '1') ? true : $check;
        }
        if (empty($check)) {
            return $this->modx->lexicon('crosscontextssettings.context_err_ns');
        }

        return true;
    }

    /**
     * {@inheritDoc}
     * @return mixed
     */
    public function process()
    {
        $ctxs = $this->getProperty('ctxs', false);
        $contexts = [];

        foreach ($ctxs as $ctx => $val) {
            if ($val == '1') {
                $contexts[] = $ctx;
            }
        }
        if (!empty($contexts)) {
            if (!$this->modx->cacheManager->refresh([
                'db' => [],
                'system_settings' => [],
                'lexicon_topics' => [],
                'auto_publish' => ['contexts' => array_diff($contexts, ['mgr'])],
                'context_settings' => ['contexts' => $contexts],
                'resource' => ['contexts' => array_diff($contexts, ['mgr'])],
                $this->modx->getOption('cache_menu_key', null, 'menu') => [],
                $this->modx->getOption('cache_action_map_key', null, 'action_map') => []
            ])) {
                return $this->failure();
            }
        }

        return $this->success();
    }
}

return 'CrossContextsSettingsContextsClearCacheProcessor';
