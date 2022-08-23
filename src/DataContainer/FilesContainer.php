<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FileManagerBundle\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\FilesModel;
use Symfony\Component\HttpFoundation\RequestStack;

class FilesContainer
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @Callback(table="tl_files", target="config.onload")
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        if (null === $dc || !$dc->id || 'edit' !== $this->requestStack->getCurrentRequest()->query->get('act')) {
            return;
        }

        $file = FilesModel::findByPath($dc->id);

        if (!$file || 'folder' !== $file->type) {
            return;
        }

        PaletteManipulator::create()
            ->addField('huhFm_folderMeta', 'default', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', 'tl_files');
    }
}
