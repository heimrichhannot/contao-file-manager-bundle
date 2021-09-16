<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FileManagerBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\UtilsBundle\Util\Utils;

/**
 * @Hook("loadDataContainer")
 */
class LoadDataContainerListener
{
    protected Utils $utils;

    public function __construct(Utils $utils)
    {
        $this->utils = $utils;
    }

    public function __invoke(string $table): void
    {
        if ($this->utils->container()->isBackend()) {
            $GLOBALS['TL_CSS']['contao-file-manager-bundle-be'] = 'bundles/heimrichhannotfilemanager/contao-file-manager-bundle-be.css|static';
        }
    }
}
