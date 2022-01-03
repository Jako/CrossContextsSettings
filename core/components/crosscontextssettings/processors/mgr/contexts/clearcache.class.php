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
                'auto_publish' => ['contexts' => array_diff($contexts, ['mgr'])],
                'system_settings' => [],
                'context_settings' => ['contexts' => $contexts],
                'lexicon_topics' => [],
                'resource' => ['contexts' => array_diff($contexts, ['mgr'])],
                'menu' => [],
                'action_map' => []
            ])) {
                return $this->failure();
            }
        }

        return $this->success();
    }
}

return 'CrossContextsSettingsContextsClearCacheProcessor';
