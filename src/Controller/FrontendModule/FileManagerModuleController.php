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
use Contao\CoreBundle\Image\PictureFactoryInterface;
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
use Symfony\Component\DependencyInjection\ContainerInterface;
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

    protected ContaoFramework         $framework;
    protected Environment             $twig;
    protected TwigTemplateLocator     $twigTemplateLocator;
    protected TranslatorInterface     $translator;
    protected HuhRequest              $request;
    protected ModelUtil               $modelUtil;
    protected UrlUtil                 $urlUtil;
    protected FileUtil                $fileUtil;
    protected FileManagerUtil         $fileManagerUtil;
    protected StatusMessageManager    $statusMessageManager;
    protected PictureFactoryInterface $pictureFactory;

    public function __construct(
        ContainerInterface $container,
        ContaoFramework $framework,
        Environment $twig,
        TwigTemplateLocator $twigTemplateLocator,
        TranslatorInterface $translator,
        HuhRequest $request,
        ModelUtil $modelUtil,
        UrlUtil $urlUtil,
        FileUtil $fileUtil,
        FileManagerUtil $fileManagerUtil,
        StatusMessageManager $statusMessageManager,
        PictureFactoryInterface $pictureFactory
    ) {
        $this->container = $container;
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
        $this->pictureFactory = $pictureFactory;
    }

    public function addEncoreAssets()
    {
        if ($this->encoreFrontendAsset) {
            $this->encoreFrontendAsset->addActiveEntrypoint('contao-file-manager-bundle');
        }
    }

    public function setEncoreFrontendAsset(\HeimrichHannot\EncoreBundle\Asset\FrontendAsset $encoreFrontendAsset): void
    {
        $this->encoreFrontendAsset = $encoreFrontendAsset;
    }

    protected function getResponse(Template $template, ModuleModel $module, Request $request): ?Response
    {
        if (null === ($fileManagerConfig = $this->modelUtil->findModelInstanceByPk('tl_file_manager_config', $module->fileManagerConfig))) {
            throw new \Exception('File manager config ID '.$module->fileManagerConfig.' not found.');
        }

        $this->framework->getAdapter(System::class)->loadLanguageFile('tl_file_manager_config');

        $this->addEncoreAssets();

        $scopeKey = $this->statusMessageManager->getScopeKey(
            StatusMessageManager::SCOPE_TYPE_MODULE,
            $module->id
        );

        $templateData = $fileManagerConfig->row();

        $templateData['scopeKey'] = $scopeKey;

        $templateName = $this->twigTemplateLocator->getTemplatePath(
            $fileManagerConfig->template ?: 'huh_file_manager_default.html.twig'
        );

        $currentFolder = $this->getCurrentFolder($request, $fileManagerConfig);

        if (!$currentFolder) {
            throw new \Exception('No initial folder defined. You can do that either in file manager config ID '.$module->fileManagerConfig.' or in the currently logged in member\'s group.');
        }

        if (!$this->fileManagerUtil->checkPermission($currentFolder, $fileManagerConfig)) {
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

        $templateData = $this->addSubFilesAndFolders($currentFolder, $templateData, $fileManagerConfig);

        if (!$fileManagerConfig->hideBreadcrumbNavigation) {
            $templateData = $this->addBreadcrumbNavigation($currentFolder, $templateData, $fileManagerConfig);
        }

        $templateData = $this->addActions($templateData, $fileManagerConfig, $request, $module);

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

    protected function addSubFilesAndFolders(Model $currentFolder, array $templateData, Model $fileManagerConfig)
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

                if ($fileManagerConfig->addThumbnailImages) {
                    $data['_thumbnailPicture'] = $this->getThumbnailImage($files->current(), $fileManagerConfig);
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

    protected function getThumbnailImage(Model $file, Model $fileManagerConfig): ?array
    {
        if (!\in_array($file->extension, explode(',', Config::get('validImageTypes')))) {
            return null;
        }

        $picture = $this->pictureFactory->create(
            $this->getParameter('kernel.project_dir').'/'.$file->path,
            StringUtil::deserialize($fileManagerConfig->thumbnailImageSize, true)
        );

        $meta = StringUtil::deserialize($file->meta, true);

        $alt = '';

        if (isset($meta[$GLOBALS['TL_LANGUAGE'] ?: 'en']['alt'])) {
            $alt = $meta[$GLOBALS['TL_LANGUAGE'] ?: 'en']['alt'];
        }

        return [
            'path' => $picture->getImg($this->getParameter('kernel.project_dir'))['src'],
            'alt' => $alt,
        ];
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

            if ($this->fileManagerUtil->checkPermission($parentFolder->path, $fileManagerConfig)) {
                $data['_href'] = $this->urlUtil->addQueryString('folder='.$parentFolder->path);
            }

            $parentFolderData[] = $data;
        }

        // add current folder
        $parentFolderData[] = $currentFolder->row();

        $templateData['breadcrumbs'] = $parentFolderData;

        return $templateData;
    }

    protected function addActions(array $templateData, Model $fileManagerConfig, Request $request, ModuleModel $module)
    {
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

        // folders
        foreach ($templateData['folderData'] as &$folder) {
            $actionsData = [];

            foreach ($actions as $action) {
                if (FileManagerConfigContainer::ACTION_UPLOAD === $action) {
                    continue;
                }

                $actionsData[$action] = [
                    'title' => $this->translator->trans('huh.file_manager.misc.'.$action),
                    'class' => $slug->generate($action),
                    'href' => $this->getActionUrl($action, $folder['uuid'], $fileManagerConfig, $request, $module),
                    'attributes' => $this->getActionAttributes($action, $folder),
                ];
            }

            $folder['_actions'] = $actionsData;
        }

        // files
        foreach ($templateData['filesData'] as &$file) {
            $actionsData = [];

            foreach ($actions as $action) {
                if (FileManagerConfigContainer::ACTION_UPLOAD === $action) {
                    continue;
                }

                $actionsData[$action] = [
                    'title' => $this->translator->trans('huh.file_manager.misc.'.$action),
                    'class' => $slug->generate($action),
                    'href' => $this->getActionUrl($action, $file['uuid'], $fileManagerConfig, $request, $module),
                    'attributes' => $this->getActionAttributes($action, $file),
                ];
            }

            $file['_actions'] = $actionsData;
        }

        if (empty($templateData['folderData']) && empty($templateData['filesData'])) {
            $templateData['emptyText'] = $this->translator->trans('huh.file_manager.message.no_files_in_folder');
        }

        return $templateData;
    }

    protected function getActionUrl(string $action, $uuid, Model $fileManagerConfig, Request $request, ModuleModel $module): string
    {
        global $objPage;

        $redirectParams = urlencode($request->getQueryString());

        switch ($action) {
            case FileManagerConfigContainer::ACTION_DELETE:
                return $this->urlUtil->addQueryString(
                    'config='.$fileManagerConfig->uuid.
                    '&redirect='.$objPage->id.($redirectParams ? '&redirect_params='.$redirectParams : '').
                    '&module='.$module->id,
                    sprintf(\Contao\Environment::get('url').ActionController::DELETE_URI, StringUtil::binToUuid($uuid)));
        }

        return '#';
    }

    protected function getActionAttributes(string $action, array $file): string
    {
        $attributes = [];

        switch ($action) {
            case FileManagerConfigContainer::ACTION_DELETE:
                $attributes['data-delete-confirm-message'] = $this->translator->trans('huh.file_manager.message.really_delete_'.$file['type'], [
                    '{name}' => $file['name'],
                ]);
        }

        $result = '';

        foreach ($attributes as $name => $value) {
            $result .= $name.'="'.htmlspecialchars($value, \ENT_QUOTES, 'UTF-8').'" ';
        }

        return trim($result);
    }
}
