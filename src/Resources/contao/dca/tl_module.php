<?php

$dca = &$GLOBALS['TL_DCA']['tl_module'];

/**
 * Palettes
 */
$dca['palettes'][\HeimrichHannot\FileManagerBundle\Controller\FrontendModule\FileManagerModuleController::TYPE] =
    '{title_legend},name,headline,type;{config_legend},fileManagerConfig;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

/**
 * Fields
 */
$fields = [
    'fileManagerConfig' => [
        'exclude'   => true,
        'filter'    => true,
        'inputType' => 'select',
        'eval'      => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
        'sql'       => "int(10) unsigned NOT NULL default '0'"
    ],
];

$dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], $fields);
