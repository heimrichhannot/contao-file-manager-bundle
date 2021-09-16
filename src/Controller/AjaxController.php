<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FileManagerBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(defaults={"_scope" = "frontend"})
 */
class AjaxController
{
    const URI = '/huh_file_manager/s';

    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @var ContaoFramework
     */
    private $framework;

    public function __construct(
        ContaoFramework $framework,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->framework = $framework;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return Response
     *
     * @Route("/huh_file_manager/")
     */
    public function progressAction(Request $request)
    {
        $this->framework->initialize();

        return new JsonResponse([]);
    }
}
