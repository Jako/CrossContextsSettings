<?php
/**
 * CrossContextsSettings connector
 *
 * @package crosscontextssettings
 * @subpackage connector
 *
 * @var modX $modx
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('crosscontextssettings.core_path', null, $modx->getOption('core_path') . 'components/crosscontextssettings/');
/** @var CrossContextsSettings $crosscontextssettings */
$crosscontextssettings = $modx->getService('crosscontextssettings', 'CrossContextsSettings', $corePath . 'model/crosscontextssettings/', [
    'core_path' => $corePath
]);

// Handle request
$modx->request->handleRequest([
    'processors_path' => $crosscontextssettings->getOption('processorsPath'),
    'location' => ''
]);
