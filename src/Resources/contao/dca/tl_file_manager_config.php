<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_file_manager_config'] = [
    'config'      => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting'           => [
            'mode'         => 5,
            'fields'       => ['title'],
            'headerFields' => ['title'],
            'panelLayout'  => 'filter;sort,search,limit',
        ],
        'global_operations' => [
            'toggleNodes'        => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['toggleAll'],
                'href'  => 'ptg=all',
                'class' => 'header_toggle',
            ],
            'sortAlphabetically' => [
                'label' => &$GLOBALS['TL_LANG']['tl_file_manager_config']['sortAlphabetically'],
                'href'  => 'key=sort_alphabetically',
                'class' => 'header_toggle',
            ],
            'all'                => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_file_manager_config']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_file_manager_config']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
//            'copyChilds' => [
//                'label' => &$GLOBALS['TL_LANG']['tl_file_manager_config']['copyChilds'],
//                'href' => 'act=paste&amp;mode=copy&amp;childs=1',
//                'icon' => 'copychilds.gif',
//                'attributes' => 'onclick="Backend.getScrollOffset()"',
//            ],
            'cut'    => [
                'label'      => &$GLOBALS['TL_LANG']['tl_file_manager_config']['cut'],
                'href'       => 'act=paste&amp;mode=cut',
                'icon'       => 'cut.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_file_manager_config']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_file_manager_config']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__' => [
        ],
        'default'      => '{general_legend},title,initialFolder;{security_legend},allowedFolders;{template_legend},template;',
    ],
    'subpalettes' => [
    ],
    'fields'      => [
        'id'                       => [
            'sql'  => 'int(10) unsigned NOT NULL auto_increment',
            'eval' => ['notOverridable' => true],
        ],
        'pid'                      => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp'                   => [
            'eval' => ['notOverridable' => true],
            'sql'  => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting'                  => [
            'eval' => ['notOverridable' => true],
            'sql'  => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded'                => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true, 'notOverridable' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'",
        ],
        // general
        'title'                    => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50', 'notOverridable' => true],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'initialFolder'            => [
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['fieldType' => 'radio', 'tl_class' => 'w50 autoheight clr'],
            'sql'       => "binary(16) NULL"
        ],
        'hideBreadcrumbNavigation' => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        // security
        'allowedFolders'           => [
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['multiple' => true, 'fieldType' => 'checkbox', 'tl_class' => 'w50 autoheight clr'],
            'sql'       => "blob NULL"
        ],
        // template
        'template'                 => [
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'eval'      => ['tl_class' => 'w50', 'includeBlankOption' => true, 'chosen' => true],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
    ],
];
