<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FileManagerBundle\Controller;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\File;
use Contao\Folder;
use HeimrichHannot\FileManagerBundle\Util\FileManagerUtil;
use HeimrichHannot\StatusMessageBundle\Manager\StatusMessageManager;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route(defaults={"_scope" = "frontend"})
 */
class ActionController
{
    const DELETE_URI = '/huh_file_manager/delete/%s';

    protected EventDispatcherInterface $eventDispatcher;
    protected ModelUtil                $modelUtil;
    protected UrlUtil                  $urlUtil;
    protected TranslatorInterface      $translator;
    protected ContainerInterface       $container;
    protected StatusMessageManager     $statusMessageManager;
    protected FileManagerUtil          $fileManagerUtil;

    /**
     * @var ContaoFramework
     */
    private $framework;

    public function __construct(
        ContainerInterface $container,
        ContaoFramework $framework,
        EventDispatcherInterface $eventDispatcher,
        ModelUtil $modelUtil,
        UrlUtil $urlUtil,
        TranslatorInterface $translator,
        StatusMessageManager $statusMessageManager,
        FileManagerUtil $fileManagerUtil
    ) {
        $this->container = $container;
        $this->framework = $framework;
        $this->eventDispatcher = $eventDispatcher;
        $this->modelUtil = $modelUtil;
        $this->urlUtil = $urlUtil;
        $this->translator = $translator;
        $this->statusMessageManager = $statusMessageManager;
        $this->fileManagerUtil = $fileManagerUtil;
    }

    /**
     * @return Response
     *
     * @Route("/huh_file_manager/delete/{uuid}")
     */
    public function progressAction(Request $request)
    {
        $this->framework->initialize();

        if (null === ($fileManagerConfig = $this->modelUtil->findOneModelInstanceBy('tl_file_manager_config', [
                'tl_file_manager_config.uuid=?',
            ], [
                $request->get('config'),
            ]))) {
            return new Response('File manager config couldn\'t be found not found.', 404);
        }

        if (null === ($model = $this->modelUtil->callModelMethod('tl_files', 'findByUuid', $request->get('uuid')))) {
            return new Response('File/folder model with given uuid couldn\'t be found.', 404);
        }

        if (!file_exists($this->container->getParameter('kernel.project_dir').'/'.$model->path)) {
            return new Response('File/folder with given path couldn\'t be found.', 404);
        }

        if (!$this->fileManagerUtil->checkPermission($model->path, $fileManagerConfig)) {
            return new Response('Access denied for the given file/folder.', 403);
        }

        if ('file' === $model->type) {
            $file = new File($model->path);

            $file->delete();
        } elseif ('folder' === $model->type) {
            $folder = new Folder($model->path);

            $folder->delete();
        } else {
            return new Response('File object has invalid type.', 500);
        }

        $successMessage = $this->translator->trans('huh.file_manager.message.'.$model->type.'_deleted_successfully', [
            '{name}' => basename($model->name),
        ]);

        if ($request->isXmlHttpRequest()) {
            return new Response($successMessage);
        }

        // redirect if synchronous request (don't allow complete redirect urls to avoid redirect exploitation
        if (null === $page = $this->modelUtil->findModelInstanceByPk('tl_page', $request->get('redirect'))) {
            return new Response('Redirect page with given ID couldn\'t be found.', 404);
        }

        $scopeKey = $this->statusMessageManager->getScopeKey(StatusMessageManager::SCOPE_TYPE_MODULE, $request->get('module'));

        $this->statusMessageManager->addSuccessMessage(
            $successMessage, $scopeKey
        );

        throw new RedirectResponseException('/'.$this->urlUtil->addQueryString(urldecode($request->get('redirect_params') ?: ''), $page->getFrontendUrl()));
    }
}
