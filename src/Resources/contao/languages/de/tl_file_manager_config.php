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

$lang['allowedFolders'][0] = 'Erlaubte Verzeichnisse (erweiterbar in Mitgliedergruppe)';
$lang['allowedFolders'][1] = 'Wählen Sie hier die Verzeichnisse aus, auf die die Nutzer der Dateiverwaltung zugreifen dürfen. Zugriff auf die Unterverzeichnisse ist damit automatisch auch gewährt.';

// template
$lang['template'][0] = 'Template';
$lang['template'][1] = 'Wählen Sie hier das gewünschte Template aus.';

/*
 * Legends
 */
$lang['general_legend'] = 'Allgemeine Einstellungen';
$lang['security_legend'] = 'Sicherheit';
$lang['template_legend'] = 'Template';
$lang['misc_legend'] = 'Verschiedenes';

/*
 * Reference
 */
$lang['reference'] = [
    'sortAlphabeticallyConfirm' => 'Möchten Sie wirklich fortfahren? Diese Aktion kann nur schwer rückgängig gemacht werden.',
];

/*
 * Buttons
 */
$lang['new'] = ['Neue Dateiverwaltungskonfiguration', 'Dateiverwaltungskonfiguration erstellen'];
$lang['edit'] = ['Dateiverwaltungskonfiguration bearbeiten', 'Dateiverwaltungskonfiguration ID %s bearbeiten'];
$lang['editheader'] = ['Dateiverwaltungskonfiguration-Einstellungen bearbeiten', 'Dateiverwaltungskonfiguration-Einstellungen ID %s bearbeiten'];
$lang['copy'] = ['Dateiverwaltungskonfiguration duplizieren', 'Dateiverwaltungskonfiguration ID %s duplizieren'];
$lang['delete'] = ['Dateiverwaltungskonfiguration löschen', 'Dateiverwaltungskonfiguration ID %s löschen'];
$lang['toggle'] = ['Dateiverwaltungskonfiguration veröffentlichen', 'Dateiverwaltungskonfiguration ID %s veröffentlichen/verstecken'];
$lang['show'] = ['Dateiverwaltungskonfiguration Details', 'Dateiverwaltungskonfiguration-Details ID %s anzeigen'];
$lang['editFilter'] = ['Filter bearbeiten', 'Den Filter ID %s bearbeiten'];
$lang['sortAlphabetically'] = ['Alphabetisch sortieren', 'Dateiverwaltungskonfigurationen alphabetisch sortieren'];
