<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$dca = $GLOBALS['TL_DCA']['tl_files'];

$dca['fields']['huhFm_folderMeta'] = [
    'inputType' => 'metaWizard',
    'eval' => [
        'allowHtml' => true,
        'multiple' => true,
        'metaFields' => [
            'title' => 'maxlength="255"',
        ],
    ],
    'sql' => 'blob NULL',
];
