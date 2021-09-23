<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FileManagerBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;

/**
 * @Hook("loadDataContainer")
 */
class LoadDataContainerListener
{
    protected static $run = false;

    protected Utils $utils;
    protected DcaUtil $dcaUtil;

    public function __construct(Utils $utils, DcaUtil $dcaUtil)
    {
        $this->utils = $utils;
        $this->dcaUtil = $dcaUtil;
    }

    public function __invoke(string $table): void
    {
        if (!static::$run) {
            static::$run = true;
        }

        if ($this->utils->container()->isBackend()) {
            $GLOBALS['TL_CSS']['contao-file-manager-bundle-be'] = 'bundles/heimrichhannotfilemanager/contao-file-manager-bundle-be.css|static';
        }

        if ($this->utils->container()->isFrontend()) {
            $GLOBALS['TL_JAVASCRIPT']['contao-file-manager-bundle'] = 'bundles/heimrichhannotfilemanager/contao-file-manager-bundle.js|static';
        }
    }
}
