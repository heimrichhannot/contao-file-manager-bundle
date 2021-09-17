<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FileManagerBundle\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use HeimrichHannot\UtilsBundle\Choice\ModelInstanceChoice;

class ModuleContainer
{
    protected ModelInstanceChoice $modelInstanceChoice;

    public function __construct(ModelInstanceChoice $modelInstanceChoice) {
        $this->modelInstanceChoice = $modelInstanceChoice;
    }

    /**
     * @Callback(table="tl_module", target="fields.fileManagerConfig.options")
     */
    public function getFileManagerConfigsAsOptions(DataContainer $dc)
    {
        return $this->modelInstanceChoice->getChoices([
            'dataContainer' => 'tl_file_manager_config'
        ]);
    }
}
