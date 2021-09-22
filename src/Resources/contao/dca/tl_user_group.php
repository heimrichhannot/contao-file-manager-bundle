<?php

$dca = &$GLOBALS['TL_DCA']['tl_user_group'];

/**
 * Palettes
 */
$dca['palettes']['default'] = str_replace('fop;', 'fop;{huh_file_manager_legend},filemanagerbundles,filemanagerbundlep;', $dca['palettes']['default']);

/**
 * Fields
 */
$fields = [
    'filemanagerbundles' => [
        'label'      => &$GLOBALS['TL_LANG']['tl_user']['filemanagerbundles'],
        'exclude'    => true,
        'inputType'  => 'checkbox',
        'foreignKey' => 'tl_file_manager_config.title',
        'eval'       => ['multiple' => true],
        'sql'        => "blob NULL"
    ],
    'filemanagerbundlep' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_user']['filemanagerbundlep'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'options'   => ['create', 'delete'],
        'reference' => &$GLOBALS['TL_LANG']['MSC'],
        'eval'      => ['multiple' => true],
        'sql'       => "blob NULL"
    ],
];

$dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], $fields);

