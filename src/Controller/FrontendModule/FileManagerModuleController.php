<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FileManagerBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(FileManagerModuleController::TYPE,category="miscellaneous")
 */
class FileManagerModuleController extends AbstractFrontendModuleController
{
    const TYPE = 'huh_file_manager';

    /**
     * @var ModelUtil
     */
    protected ModelUtil $modelUtil;

    public function __construct(ModelUtil $modelUtil)
    {
        $this->modelUtil = $modelUtil;
    }

    protected function getResponse(Template $template, ModuleModel $module, Request $request): ?Response
    {
        if (null === ($target = $this->modelUtil->findModelInstanceByPk('tl_page', $module->jumpTo))) {
            return new Response('');
        }

        global $objPage;

        $template->href = $target->getFrontendUrl();

        if ($objPage->id == $module->jumpTo) {
            $template->active = true;
        }

        return $template->getResponse();
    }
}
