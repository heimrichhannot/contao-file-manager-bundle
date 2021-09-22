<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FileManagerBundle\Util;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendUser;
use HeimrichHannot\UtilsBundle\Member\MemberUtil;
use Model\Collection;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FileManagerUtil
{
    protected ContaoFramework  $framework;
    protected SessionInterface $session;
    protected MemberUtil       $memberUtil;

    public function __construct(ContaoFramework $framework, SessionInterface $session, MemberUtil $memberUtil) {
        $this->framework = $framework;
        $this->session = $session;
        $this->memberUtil = $memberUtil;
    }

    public function getCurrentMemberGroups(): ?Collection
    {
        $member = $this->framework->getAdapter(FrontendUser::class)->getInstance();

        if (null === $member) {
            return new Collection([], 'tl_member_group');
        }

        return $this->memberUtil->getActiveGroups($member->id);
    }
}
