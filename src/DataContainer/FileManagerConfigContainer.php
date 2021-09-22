<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FileManagerBundle\DataContainer;

use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\RequestToken;
use Contao\StringUtil;
use Contao\Versions;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;

class FileManagerConfigContainer
{
    const ACTION_UPLOAD = 'upload';
    const ACTION_RENAME = 'rename';
    const ACTION_COPY   = 'copy';
    const ACTION_MOVE   = 'move';
    const ACTION_DELETE = 'delete';

    const ACTIONS = [
//        self::ACTION_UPLOAD,
//        self::ACTION_RENAME,
//        self::ACTION_COPY,
//        self::ACTION_MOVE,
        self::ACTION_DELETE
    ];

    protected Request             $request;
    protected ModelUtil           $modelUtil;
    protected UrlUtil             $urlUtil;
    protected DcaUtil             $dcaUtil;
    protected TwigTemplateLocator $twigTemplateLocator;

    public function __construct(
        Request $request,
        TwigTemplateLocator $twigTemplateLocator,
        ModelUtil $modelUtil,
        UrlUtil $urlUtil,
        DcaUtil $dcaUtil
    ) {
        $this->request             = $request;
        $this->twigTemplateLocator = $twigTemplateLocator;
        $this->modelUtil           = $modelUtil;
        $this->urlUtil             = $urlUtil;
        $this->dcaUtil             = $dcaUtil;
    }

    /**
     * @Callback(table="tl_file_manager_config", target="list.global_operations.sortAlphabetically.button")
     */
    public function sortAlphabetically()
    {
        // sort alphabetically
        if ('sortAlphabetically' === $this->request->getGet('key')) {
            if (null !== ($fileManagerConfigs = $this->modelUtil->findAllModelInstances('tl_file_manager_config', [
                    'order' => 'title ASC',
                ]))) {
                $sorting = 64;

                while ($fileManagerConfigs->next()) {
                    $sorting += 64;

                    $fileManagerConfig = $fileManagerConfigs->current();

                    // The sorting has not changed
                    if ($sorting == $fileManagerConfig->sorting) {
                        continue;
                    }

                    // Initialize the version manager
                    $versions = new Versions('tl_file_manager_config', $fileManagerConfig->id);
                    $versions->initialize();

                    // Store the new alias
                    Database::getInstance()->prepare('UPDATE tl_file_manager_config SET sorting=? WHERE id=?')
                        ->execute($sorting, $fileManagerConfig->id);

                    // Create a new version
                    $versions->create();
                }
            }

            throw new RedirectResponseException($this->urlUtil->removeQueryString(['key']));
        }

        return '<a href="' . $this->urlUtil->addQueryString('key=sortAlphabetically') . '" class="header_new" style="background-image: url(system/themes/flexible/icons/rows.svg)" title="' . $GLOBALS['TL_LANG']['tl_file_manager_config']['sortAlphabetically'][1] . '" accesskey="n" onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['tl_file_manager_config']['reference']['sortAlphabeticallyConfirm'] . '\'))return false;Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['tl_file_manager_config']['sortAlphabetically'][0] . '</a>';
    }

    /**
     * @Callback(table="tl_file_manager_config", target="config.onsubmit")
     */
    public function setDateAdded(DataContainer $dc)
    {
        $this->dcaUtil->setDateAdded($dc);
    }

    /**
     * @Callback(table="tl_file_manager_config", target="config.oncopy")
     */
    public function setDateAddedOnCopy($insertId, DataContainer $dc)
    {
        $this->dcaUtil->setDateAddedOnCopy($insertId, $dc);
    }

    /**
     * @Callback(table="tl_file_manager_config", target="fields.uuid.load")
     */
    public function setUuid($value, DataContainer $dc)
    {
        // keep uuid if set
        if ($value) {
            $GLOBALS['TL_DCA']['tl_file_manager_config']['fields']['uuid']['eval']['readonly'] = true;

            return $value;
        }

        return md5(uniqid(rand(), true));
    }

    /**
     * @Callback(table="tl_file_manager_config", target="list.sorting.paste_button")
     */
    public function pasteFileManagerConfig(DataContainer $dc, $row, $table, $cr, $arrClipboard = null)
    {
        $disablePA = false;
        $disablePI = false;

        // Disable all buttons if there is a circular reference
        if (false !== $arrClipboard && ('cut' === $arrClipboard['mode'] && (1 === $cr || $arrClipboard['id'] === $row['id']) || 'cutAll' === $arrClipboard['mode'] && (1 === $cr || \in_array($row['id'], $arrClipboard['id'], true)))) {
            $disablePA = true;
            $disablePI = true;
        }

        $return = '';

        // Return the buttons
        $imagePasteAfter = Image::getHtml('pasteafter.svg', sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id']));
        $imagePasteInto  = Image::getHtml('pasteinto.svg', sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id']));

        if ($row['id'] > 0) {
            $return = $disablePA ? Image::getHtml('pasteafter_.svg') . ' ' : '<a href="' . Controller::addToUrl('act=' . $arrClipboard['mode'] . '&mode=1&rt=' . RequestToken::get() . '&pid=' . $row['id'] . (!\is_array($arrClipboard['id']) ? '&id=' . $arrClipboard['id'] : '')) . '" title="' . StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1],
                    $row['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteAfter . '</a> ';
        }

        return $return . ($disablePI ? Image::getHtml('pasteinto_.svg') . ' ' : '<a href="' . Controller::addToUrl('act=' . $arrClipboard['mode'] . '&mode=2&rt=' . RequestToken::get() . '&pid=' . $row['id'] . (!\is_array($arrClipboard['id']) ? '&id=' . $arrClipboard['id'] : '')) . '" title="' . StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1],
                    $row['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteInto . '</a> ');
    }

    /**
     * @Callback(table="tl_file_manager_config", target="list.operations.edit.button")
     */
    public function edit($row, $href, $label, $title, $icon, $attributes)
    {
        return BackendUser::getInstance()->canEditFieldsOf('tl_file_manager_config') ? '<a href="' . Controller::addToUrl($href . '&amp;id=' . $row['id']) . '&rt=' . RequestToken::get() . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
    }

    /**
     * @Callback(table="tl_file_manager_config", target="fields.template.options")
     */
    public function getInsertTagAddItemTemplates(DataContainer $dc)
    {
        return $this->twigTemplateLocator->getPrefixedFiles(
            'huh_file_manager_'
        );
    }
}
