<?php

$dca = &$GLOBALS['TL_DCA']['tl_member_group'];

/**
 * Palettes
 */
\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('huh_file_manager_legend', 'account_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addField('huhInitialFolder', 'huh_file_manager_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('huhAllowedFolders', 'huh_file_manager_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_member_group');

/**
 * Fields
 */
$fields = [
    'huhInitialFolder'  => [
        'exclude'   => true,
        'inputType' => 'fileTree',
        'eval'      => ['fieldType' => 'radio', 'tl_class' => 'w50 autoheight clr'],
        'sql'       => "binary(16) NULL"
    ],
    'huhAllowedFolders' => [
        'exclude'   => true,
        'inputType' => 'fileTree',
        'eval'      => ['multiple' => true, 'fieldType' => 'checkbox', 'tl_class' => 'w50 autoheight'],
        'sql'       => "blob NULL"
    ],
];

$dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], $fields);

