<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FileManagerBundle\Controller\FrontendModule;

use Ausi\SlugGenerator\SlugGenerator;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\File;
use Contao\Model;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use HeimrichHannot\FileManagerBundle\Controller\ActionController;
use HeimrichHannot\FileManagerBundle\DataContainer\FileManagerConfigContainer;
use HeimrichHannot\FileManagerBundle\Util\FileManagerUtil;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request as HuhRequest;
use HeimrichHannot\StatusMessageBundle\Manager\StatusMessageManager;
use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * @FrontendModule(FileManagerModuleController::TYPE,category="miscellaneous")
 */
class FileManagerModuleController extends AbstractFrontendModuleController
{
    const TYPE = 'huh_file_manager';

    protected ContaoFramework      $framework;
    protected Environment          $twig;
    protected TwigTemplateLocator  $twigTemplateLocator;
    protected TranslatorInterface  $translator;
    protected HuhRequest           $request;
    protected ModelUtil            $modelUtil;
    protected UrlUtil              $urlUtil;
    protected FileUtil             $fileUtil;
    protected FileManagerUtil      $fileManagerUtil;
    protected StatusMessageManager $statusMessageManager;

    public function __construct(
        ContaoFramework $framework,
        Environment $twig,
        TwigTemplateLocator $twigTemplateLocator,
        TranslatorInterface $translator,
        HuhRequest $request,
        ModelUtil $modelUtil,
        UrlUtil $urlUtil,
        FileUtil $fileUtil,
        FileManagerUtil $fileManagerUtil,
        StatusMessageManager $statusMessageManager
    ) {
        $this->framework = $framework;
        $this->twig = $twig;
        $this->twigTemplateLocator = $twigTemplateLocator;
        $this->translator = $translator;
        $this->request = $request;
        $this->modelUtil = $modelUtil;
        $this->urlUtil = $urlUtil;
        $this->fileUtil = $fileUtil;
        $this->fileManagerUtil = $fileManagerUtil;
        $this->statusMessageManager = $statusMessageManager;
    }

    protected function getResponse(Template $template, ModuleModel $module, Request $request): ?Response
    {
        if (null === ($fileManagerConfig = $this->modelUtil->findModelInstanceByPk('tl_file_manager_config', $module->fileManagerConfig))) {
            throw new \Exception('File manager config ID '.$module->fileManagerConfig.' not found.');
        }

        $this->framework->getAdapter(System::class)->loadLanguageFile('tl_file_manager_config');

        $scopeKey = $this->statusMessageManager->getScopeKey(
            StatusMessageManager::SCOPE_TYPE_MODULE,
            $module->id
        );

        $templateData = [
            'scopeKey' => $scopeKey,
        ];

        $templateName = $this->twigTemplateLocator->getTemplatePath(
            $fileManagerConfig->template ?: 'huh_file_manager_default.html.twig'
        );

        $currentFolder = $this->getCurrentFolder($request, $fileManagerConfig);

        if (!$currentFolder) {
            throw new \Exception('No initial folder defined. You can do that either in file manager config ID '.$module->fileManagerConfig.' or in the currently logged in member\'s group.');
        }

        if (!$this->checkPermission($currentFolder, $fileManagerConfig)) {
            $this->statusMessageManager->addErrorMessage(
                $this->translator->trans('huh.file_manager.message.access_denied', [
                    '{folder}' => $currentFolder,
                ]),
                $scopeKey
            );

            $template->content = $this->twig->render($templateName, $templateData);

            return $template->getResponse();
        }

        if ($file = $this->request->getGet('file')) {
            $this->framework->getAdapter(Controller::class)->sendFileToBrowser($file);
        }

        $currentFolder = $this->modelUtil->callModelMethod('tl_files', 'findByPath', $currentFolder);

        $templateData = $this->addSubFilesAndFolders($currentFolder, $templateData);

        if (!$fileManagerConfig->hideBreadcrumbNavigation) {
            $templateData = $this->addBreadcrumbNavigation($currentFolder, $templateData, $fileManagerConfig);
        }

        $templateData = $this->addActions($templateData, $fileManagerConfig, $request);

        $template->content = $this->twig->render($templateName, $templateData);

        return $template->getResponse();
    }

    protected function getCurrentFolder(Request $request, Model $fileManagerConfig): string
    {
        if ($request->get('folder')) {
            return $request->get('folder');
        }

        $currentFolder = $this->fileUtil->getPathFromUuid($fileManagerConfig->initialFolder);

        $groups = $this->fileManagerUtil->getCurrentMemberGroups();

        foreach ($groups as $group) {
            if ($group->huhInitialFolder) {
                $currentFolder = $this->fileUtil->getPathFromUuid($group->huhInitialFolder);

                break;
            }
        }

        // TODO event

        return $currentFolder;
    }

    protected function addSubFilesAndFolders(Model $currentFolder, array $templateData)
    {
        $options = [
            'order' => 'name ASC',
        ];

        if ($currentFolder->path) {
            $folders = $this->modelUtil->callModelMethod('tl_files', 'findMultipleFoldersByFolder', $currentFolder->path, $options);
            $files = $this->modelUtil->callModelMethod('tl_files', 'findMultipleFilesByFolder', $currentFolder->path, $options);
        } else {
            $folders = $this->modelUtil->findModelInstancesBy('tl_files', [
                'tl_files.type=?',
                'tl_files.pid IS NULL',
            ], [
                'folder',
            ], $options);

            $files = $this->modelUtil->findModelInstancesBy('tl_files', [
                'tl_files.type=?',
                'tl_files.pid IS NULL',
            ], [
                'file',
            ], $options);
        }

        $folderData = [];

        if (null !== $folders) {
            while ($folders->next()) {
                $data = $folders->row();

                $data['_modified'] = date(Config::get('datimFormat'), $folders->tstamp);
                $data['_href'] = $this->urlUtil->addQueryString('folder='.$folders->path);

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

                $data['_href'] = $this->urlUtil->addQueryString('file='.$files->path);
                $data['_modified'] = date(Config::get('datimFormat'), $files->tstamp);
                $data['_size'] = System::getReadableSize($file->filesize);

                $filesData[] = $data;
            }
        }

        $templateData['filesData'] = $filesData;
        $templateData['folderData'] = $folderData;

        return $templateData;
    }

    protected function addBreadcrumbNavigation(Model $currentFolder, array $templateData, Model $fileManagerConfig)
    {
        // add parent folders
        $parentFolders = $this->fileUtil->getParentFoldersByUuid($currentFolder->uuid, [
            'returnRows' => true,
        ]);

        $parentFolderData = [];

        foreach (array_reverse($parentFolders) as $parentFolder) {
            $data = $parentFolder->row();

            if ($this->checkPermission($parentFolder->path, $fileManagerConfig)) {
                $data['_href'] = $this->urlUtil->addQueryString('folder='.$parentFolder->path);
            }

            $parentFolderData[] = $data;
        }

        // add current folder
        $parentFolderData[] = $currentFolder->row();

        $templateData['breadcrumbs'] = $parentFolderData;

        return $templateData;
    }

    protected function addActions(array $templateData, Model $fileManagerConfig, Request $request)
    {
        global $objPage;

        $actions = StringUtil::deserialize($fileManagerConfig->allowedActions, true);

        $groups = $this->fileManagerUtil->getCurrentMemberGroups();

        foreach ($groups as $group) {
            $actions = array_merge($actions, StringUtil::deserialize($group->huhAllowedActions, true));
        }

        if (empty($actions)) {
            return $templateData;
        }

        $templateData['actions'] = $actions;

        $slug = new SlugGenerator();
        $redirectParams = urlencode($request->getQueryString());

        // folders
        foreach ($templateData['folderData'] as &$folder) {
            $actionsData = [];

            foreach ($actions as $action) {
                if (FileManagerConfigContainer::ACTION_UPLOAD === $action) {
                    continue;
                }

                $actionsData[$action] = [
                    'title' => $GLOBALS['TL_LANG']['tl_file_manager_config']['reference'][$action],
                    'class' => $slug->generate($action),
                    'href' => $this->urlUtil->addQueryString(
                        'config='.$fileManagerConfig->uuid.'&redirect='.$objPage->id.($redirectParams ? '&redirect_params='.$redirectParams : ''),
                        sprintf(\Contao\Environment::get('url').ActionController::DELETE_URI, StringUtil::binToUuid($folder['uuid']))
                    ),
                ];
            }

            $folder['_actions'] = $actionsData;
        }

        // files
        foreach ($templateData['filesData'] as &$files) {
            $actionsData = [];

            foreach ($actions as $action) {
                if (FileManagerConfigContainer::ACTION_UPLOAD === $action) {
                    continue;
                }

                $actionsData[$action] = [
                    'title' => $GLOBALS['TL_LANG']['tl_file_manager_config']['reference'][$action],
                    'class' => $slug->generate($action),
                    'href' => $this->urlUtil->addQueryString(
                        'config='.$fileManagerConfig->uuid.'&redirect='.$objPage->id.($redirectParams ? '&redirect_params='.$redirectParams : ''),
                        sprintf(\Contao\Environment::get('url').ActionController::DELETE_URI, StringUtil::binToUuid($files['uuid']))
                    ),
                ];
            }

            $files['_actions'] = $actionsData;
        }

        return $templateData;
    }

    protected function checkPermission(string $currentFolder, Model $fileManagerConfig)
    {
        // check if given folder exists
        if (null === ($model = $this->modelUtil->callModelMethod('tl_files', 'findByPath', $currentFolder))) {
            return false;
        }

        // check file mounts...
        $parentFolders = $this->fileUtil->getParentFoldersByUuid($model->uuid, [
            'returnRows' => true,
        ]);

        $parentFolders = array_map(function ($row) {
            return $row->uuid;
        }, $parentFolders);

        // ... in file manager config
        foreach (StringUtil::deserialize($fileManagerConfig->allowedFolders, true) as $allowedFolder) {
            if ($model->uuid === $allowedFolder || \in_array($allowedFolder, $parentFolders)) {
                return true;
            }
        }

        // ... in member group
        $groups = $this->fileManagerUtil->getCurrentMemberGroups();

        foreach ($groups as $group) {
            if ($group->huhAllowedFolders) {
                foreach (StringUtil::deserialize($group->huhAllowedFolders, true) as $allowedFolder) {
                    if ($model->uuid === $allowedFolder || \in_array($allowedFolder, $parentFolders)) {
                        return true;
                    }
                }
            }
        }

        // TODO event

        return false;
    }
}
