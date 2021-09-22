<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['tl_file_manager_config'];

/*
 * Fields
 */
$lang['tstamp'][0] = 'Änderungsdatum';

// general
$lang['title'][0] = 'Titel';
$lang['title'][1] = 'Geben Sie hier bitte den Titel ein.';

$lang['initialFolder'][0] = 'Initiales Verzeichnis (erweiterbar in Mitgliedergruppe)';
$lang['initialFolder'][1] = 'Wählen Sie das Verzeichnis aus, das dargestellt werden soll, wenn noch keines ausgewählt wurde.';

$lang['hideBreadcrumbNavigation'][0] = 'Breadcrumb-Navigation ausblenden';
$lang['hideBreadcrumbNavigation'][1] = 'Wählen Sie diese Option, um die Breadcrumb-Navigation auszublenden.';

// security
$lang['allowedActions'][0] = 'Erlaubte Aktionen (erweiterbar in Mitgliedergruppe)';
$lang['allowedActions'][1] = 'Wählen Sie hier die Aktionen, die verfügbar sein sollen.';

$lang['allowedFolders'][0] = 'Erlaubte Verzeichnisse (erweiterbar in Mitgliedergruppe)';
$lang['allowedFolders'][1] = 'Wählen Sie hier die Verzeichnisse aus, auf die die Nutzer der Dateiverwaltung zugreifen dürfen. Zugriff auf die Unterverzeichnisse ist damit automatisch auch gewährt.';

$lang['uuid'][0] = 'Eindeutige ID (UUID)';
$lang['uuid'][1] = 'In diesem Feld wird die eindeutige ID gespeichert. Sie wird genutzt, um unbefugte Zugriffe zu unterbinden.';

// template
$lang['template'][0] = 'Template';
$lang['template'][1] = 'Wählen Sie hier das gewünschte Template aus.';

/*
 * Legends
 */
$lang['general_legend']  = 'Allgemeine Einstellungen';
$lang['security_legend'] = 'Sicherheit';
$lang['template_legend'] = 'Template';
$lang['misc_legend']     = 'Verschiedenes';

/*
 * Reference
 */
$lang['reference'] = [
    'sortAlphabeticallyConfirm'                                                               => 'Möchten Sie wirklich fortfahren? Diese Aktion kann nur schwer rückgängig gemacht werden.',
    \HeimrichHannot\FileManagerBundle\DataContainer\FileManagerConfigContainer::ACTION_UPLOAD => 'Upload neuer Dateien',
    \HeimrichHannot\FileManagerBundle\DataContainer\FileManagerConfigContainer::ACTION_COPY   => 'Dateien/Verzeichnisse kopieren',
    \HeimrichHannot\FileManagerBundle\DataContainer\FileManagerConfigContainer::ACTION_MOVE   => 'Dateien/Verzeichnisse verschieben',
    \HeimrichHannot\FileManagerBundle\DataContainer\FileManagerConfigContainer::ACTION_RENAME => 'Dateien/Verzeichnisse umbenennen',
    \HeimrichHannot\FileManagerBundle\DataContainer\FileManagerConfigContainer::ACTION_DELETE => 'Dateien/Verzeichnisse löschen',
];

/*
 * Buttons
 */
$lang['new']                = ['Neue Dateiverwaltungskonfiguration', 'Dateiverwaltungskonfiguration erstellen'];
$lang['edit']               = ['Dateiverwaltungskonfiguration bearbeiten', 'Dateiverwaltungskonfiguration ID %s bearbeiten'];
$lang['editheader']         = ['Dateiverwaltungskonfiguration-Einstellungen bearbeiten', 'Dateiverwaltungskonfiguration-Einstellungen ID %s bearbeiten'];
$lang['copy']               = ['Dateiverwaltungskonfiguration duplizieren', 'Dateiverwaltungskonfiguration ID %s duplizieren'];
$lang['delete']             = ['Dateiverwaltungskonfiguration löschen', 'Dateiverwaltungskonfiguration ID %s löschen'];
$lang['toggle']             = ['Dateiverwaltungskonfiguration veröffentlichen', 'Dateiverwaltungskonfiguration ID %s veröffentlichen/verstecken'];
$lang['show']               = ['Dateiverwaltungskonfiguration Details', 'Dateiverwaltungskonfiguration-Details ID %s anzeigen'];
$lang['editFilter']         = ['Filter bearbeiten', 'Den Filter ID %s bearbeiten'];
$lang['sortAlphabetically'] = ['Alphabetisch sortieren', 'Dateiverwaltungskonfigurationen alphabetisch sortieren'];
