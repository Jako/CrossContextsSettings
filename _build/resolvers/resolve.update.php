<?php
/**
 *
 * @package crosscontextssettings
 * @subpackage build
 *
 * @var array $options
 * @var xPDOObject $object
 */

if ($object->xpdo) {
    if (!function_exists('recursiveRemoveFolder')) {
        function recursiveRemoveFolder($dir)
        {
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? recursiveRemoveFolder($dir . '/' . $file) : unlink($dir . '/' . $file);
            }
            return rmdir($dir);
        }
    }

    if (!function_exists('cleanupFolders')) {
        function cleanupFolders($modx, $corePath, $assetsPath, $cleanup)
        {
            $paths = [
                'core' => $corePath,
                'assets' => $assetsPath,
            ];
            $countFiles = 0;
            $countFolders = 0;
            foreach ($cleanup as $folder => $files) {
                foreach ($files as $file) {
                    $legacyFile = $paths[$folder] . $file;
                    if (file_exists($legacyFile)) {
                        if (is_dir($legacyFile)) {
                            recursiveRemoveFolder($legacyFile);
                            $countFolders++;
                        } else {
                            unlink($legacyFile);
                            $countFiles++;
                        }
                    }
                }
            }
            if ($countFolders || $countFiles) {
                $modx->log(xPDO::LOG_LEVEL_INFO, 'Removed ' . $countFiles . ' legacy files and ' . $countFolders . ' legacy folders of CrossContextsSettings 1.0.x');
            }
        }
    }

    if (!function_exists('cleanupMenu')) {
        function cleanupMenu($modx, $namespace, $newAction)
        {
            /** @var modAction[] $actions */
            $actions = $modx->getIterator('modAction', array(
                'namespace:=' => $namespace,
                'controller' => 'index'
            ));
            foreach ($actions as $action) {
                /** @var modMenu $menu */
                $menu = $modx->getObject('modMenu', $action->get('id'));
                if ($menu) {
                    $menu->set('action', $newAction);
                    $menu->save();
                }
                $action->remove();
            }
        }
    }

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            /** @var modX $modx */
            $modx =& $object->xpdo;
            // http://forums.modx.com/thread/88734/package-version-check#dis-post-489104
            $c = $modx->newQuery('transport.modTransportPackage');
            $c->where([
                'workspace' => 1,
                "(SELECT
                        `signature`
                      FROM {$modx->getTableName('modTransportPackage')} AS `latestPackage`
                      WHERE `latestPackage`.`package_name` = `modTransportPackage`.`package_name`
                      ORDER BY
                         `latestPackage`.`version_major` DESC,
                         `latestPackage`.`version_minor` DESC,
                         `latestPackage`.`version_patch` DESC,
                         IF(`release` = '' OR `release` = 'ga' OR `release` = 'pl','z',`release`) DESC,
                         `latestPackage`.`release_index` DESC
                      LIMIT 1,1) = `modTransportPackage`.`signature`",
            ]);
            $c->where([
                [
                    'modTransportPackage.package_name' => 'crosscontextssettings',
                    'OR:modTransportPackage.package_name:=' => 'CrossContextsSettings',
                ],
                'installed:IS NOT' => null
            ]);
            /** @var modTransportPackage $oldPackage */
            $oldPackage = $modx->getObject('transport.modTransportPackage', $c);
            $corePath = $modx->getOption('crosscontextssettings.core_path', null, $modx->getOption('coee_path', null, MODX_CORE_PATH) . 'components/crosscontextssettings/');
            $assetsPath = $modx->getOption('crosscontextssettings.assets_path', null, $modx->getOption('assets_path', null, MODX_ASSETS_PATH) . 'components/crosscontextssettings/');

            if ($oldPackage && $oldPackage->compareVersion('1.1.0-pl', '>')) {
                cleanupMenu($modx, 'crosscontextssettings', 'home');

                $cleanup = [
                    'core' => [
                        'docs/changelog.txt',
                        'docs/license.txt',
                        'docs/readme.txt',
                        'model/crosscontextssettings.class.php',
                    ],
                    'assets' => [
                        'conn',
                        'css/mgr.css',
                        'js/mgr/sections',
                        'js/mgr/widgets',
                        'js/mgr/crosscontextssettings.js',
                    ]
                ];
                cleanupFolders($modx, $corePath, $assetsPath, $cleanup);
            }

            break;
    }
}
return true;
