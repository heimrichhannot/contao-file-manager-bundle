<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['system']['file_manager_configs'] = [
    'tables' => ['tl_file_manager_config'],
];

/**
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'filemanagerbundles';
$GLOBALS['TL_PERMISSIONS'][] = 'filemanagerbundlep';

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_file_manager_config'] = 'HeimrichHannot\FileManagerBundle\Model\FileManagerConfigModel';
