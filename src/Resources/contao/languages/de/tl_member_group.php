<?php

$lang = &$GLOBALS['TL_LANG']['tl_member_group'];

/**
 * Fields
 */
$lang['huhInitialFolder'][0] = 'Initiales Verzeichnis';
$lang['huhInitialFolder'][1] = 'Wählen Sie das Verzeichnis aus, das dargestellt werden soll, wenn noch keines ausgewählt wurde.';

$lang['huhAllowedFolders'][0] = 'Erlaubte Verzeichnisse';
$lang['huhAllowedFolders'][1] = 'Wählen Sie hier die Verzeichnisse aus, auf die die Nutzer der Dateiverwaltung im Frontend zugreifen dürfen. Zugriff auf die Unterverzeichnisse ist damit automatisch auch gewährt.';

$lang['huhAllowedActions'][0] = 'Erlaubte Aktionen';
$lang['huhAllowedActions'][1] = 'Wählen Sie hier die Aktionen, die verfügbar sein sollen.';

/*
 * Reference
 */
$lang['reference']['huhFileManager'] = [
    \HeimrichHannot\FileManagerBundle\DataContainer\FileManagerConfigContainer::ACTION_UPLOAD => 'Upload neuer Dateien',
    \HeimrichHannot\FileManagerBundle\DataContainer\FileManagerConfigContainer::ACTION_COPY   => 'Dateien/Verzeichnisse kopieren',
    \HeimrichHannot\FileManagerBundle\DataContainer\FileManagerConfigContainer::ACTION_MOVE   => 'Dateien/Verzeichnisse verschieben',
    \HeimrichHannot\FileManagerBundle\DataContainer\FileManagerConfigContainer::ACTION_RENAME => 'Dateien/Verzeichnisse umbenennen',
    \HeimrichHannot\FileManagerBundle\DataContainer\FileManagerConfigContainer::ACTION_DELETE => 'Dateien/Verzeichnisse löschen',
];

/**
 * Legends
 */
$lang['huh_file_manager_legend'] = 'H&H File Manager';
