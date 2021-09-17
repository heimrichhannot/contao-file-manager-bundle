<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FileManagerBundle\Controller\FrontendModule;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\Database;
use Contao\Model;
use Contao\StringUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Contao\File;
use Contao\ModuleModel;
use Contao\System;
use Contao\Template;
use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request as HuhRequest;

/**
 * @FrontendModule(FileManagerModuleController::TYPE,category="miscellaneous")
 */
class FileManagerModuleController extends AbstractFrontendModuleController
{
    const TYPE = 'huh_file_manager';

    protected ContaoFramework     $framework;
    protected Environment         $twig;
    protected TwigTemplateLocator $twigTemplateLocator;
    protected TranslatorInterface $translator;
    protected HuhRequest          $request;
    protected ModelUtil           $modelUtil;
    protected UrlUtil             $urlUtil;
    protected FileUtil            $fileUtil;

    public function __construct(
        ContaoFramework $framework,
        Environment $twig,
        TwigTemplateLocator $twigTemplateLocator,
        TranslatorInterface $translator,
        HuhRequest $request,
        ModelUtil $modelUtil,
        UrlUtil $urlUtil,
        FileUtil $fileUtil
    )
    {
        $this->framework = $framework;
        $this->twig = $twig;
        $this->twigTemplateLocator = $twigTemplateLocator;
        $this->translator = $translator;
        $this->request = $request;
        $this->modelUtil = $modelUtil;
        $this->urlUtil = $urlUtil;
        $this->fileUtil = $fileUtil;
    }

    protected function getResponse(Template $template, ModuleModel $module, Request $request): ?Response
    {
        if (null === ($fileManagerConfig = $this->modelUtil->findModelInstanceByPk('tl_file_manager_config', $module->fileManagerConfig))) {
            throw new \Exception('File manager config ID ' . $module->fileManagerConfig . ' not found.');
        }

        $templateName = $this->twigTemplateLocator->getTemplatePath(
            $fileManagerConfig->template ?: 'huh_file_manager_default.html.twig'
        );

        $currentFolder = $request->get('folder') ?: $this->fileUtil->getPathFromUuid($fileManagerConfig->initialFolder);

        if (!$this->checkPermission($currentFolder, $fileManagerConfig)) {
            $template->content = $this->twig->render($templateName, [
                'hasError' => true,
                'message' => $this->translator->trans('huh.file_manager.message.access_denied', [
                    '{folder}' => $currentFolder
                ])
            ]);

            return $template->getResponse();
        }

        if ($file = $this->request->getGet('file')) {
            $this->framework->getAdapter(Controller::class)->sendFileToBrowser($file);
        }

        $currentFolder = $this->modelUtil->callModelMethod('tl_files', 'findByPath', $currentFolder);

        $templateData = [];

        $templateData = $this->addSubFilesAndFolders($currentFolder, $templateData);

        if (!$fileManagerConfig->hideBreadcrumbNavigation) {
            $templateData = $this->addBreadcrumbNavigation($currentFolder, $templateData);
        }

        $template->content = $this->twig->render($templateName, $templateData);

        return $template->getResponse();
    }

    protected function addSubFilesAndFolders(Model $currentFolder, array $templateData)
    {
        $options = [
            'order' => 'name ASC'
        ];

        if ($currentFolder->path) {
            $folders = $this->modelUtil->callModelMethod('tl_files', 'findMultipleFoldersByFolder', $currentFolder->path, $options);
            $files = $this->modelUtil->callModelMethod('tl_files', 'findMultipleFilesByFolder', $currentFolder->path, $options);
        } else {
            $folders = $this->modelUtil->findModelInstancesBy('tl_files', [
                'tl_files.type=?',
                'tl_files.pid IS NULL'
            ], [
                'folder'
            ], $options);

            $files = $this->modelUtil->findModelInstancesBy('tl_files', [
                'tl_files.type=?',
                'tl_files.pid IS NULL'
            ], [
                'file'
            ], $options);
        }

        $folderData = [];

        if (null !== $folders) {
            while ($folders->next()) {
                $data = $folders->row();

                $data['_modified'] = date(Config::get('datimFormat'), $folders->tstamp);
                $data['_href'] = $this->urlUtil->addQueryString('folder=' . $folders->path);

                $folderData[] = $data;
            }
        }

        $filesData = [];

        if (null !== $files) {
            while ($files->next()) {
                $data = $files->row();

                $file = new File($files->path);

                if (!$file->exists()) {
                    continue;
                }

                $data['_href'] = $this->urlUtil->addQueryString('file=' . $files->path);
                $data['_modified'] = date(Config::get('datimFormat'), $files->tstamp);
                $data['_size'] = System::getReadableSize($file->filesize);

                $filesData[] = $data;
            }
        }

        $templateData['filesData'] = $filesData;
        $templateData['folderData'] = $folderData;

        return $templateData;
    }

    protected function addBreadcrumbNavigation(Model $currentFolder, array $templateData) {
        // add parent folders
        $parentFolders = $this->fileUtil->getParentFoldersByUuid($currentFolder->uuid, [
            'returnRows' => true
        ]);

        $parentFolderData = [];

        foreach (array_reverse($parentFolders) as $parentFolder) {
            $data = $parentFolder->row();

            $data['_href'] = $this->urlUtil->addQueryString('folder=' . $parentFolder->path);

            $parentFolderData[] = $data;
        }

        // add current folder
        $parentFolderData[] = $currentFolder->row();

        $templateData['breadcrumbs'] = $parentFolderData;

        return $templateData;
    }

    protected function checkPermission(string $currentFolder, Model $fileManagerConfig)
    {
        // check if given folder exists
        if (null === ($model = $this->modelUtil->callModelMethod('tl_files', 'findByPath', $currentFolder)))
        {
            return false;
        }

        // check file mounts
        $fileMounts = StringUtil::deserialize($fileManagerConfig->fileMounts, true);

        if (!empty($fileMounts)) {
            $parentFolders = $this->fileUtil->getParentFoldersByUuid($model->uuid, [
                'returnRows' => true
            ]);

            $parentFolders = array_map(function($row) {
                return $row->uuid;
            }, $parentFolders);

            $found = false;

            foreach ($fileMounts as $fileMount) {
                if ($model->uuid === $fileMount || in_array($fileMount, $parentFolders)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                return false;
            }
        }

        return true;
    }
}
