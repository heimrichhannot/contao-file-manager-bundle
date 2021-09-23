<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FileManagerBundle\Util;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendUser;
use Contao\Model;
use Contao\StringUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Member\MemberUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Model\Collection;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FileManagerUtil
{
    protected ContaoFramework  $framework;
    protected SessionInterface $session;
    protected MemberUtil       $memberUtil;
    protected ModelUtil        $modelUtil;
    protected FileUtil         $fileUtil;

    public function __construct(
        ContaoFramework $framework,
        SessionInterface $session,
        MemberUtil $memberUtil,
        ModelUtil $modelUtil,
        FileUtil $fileUtil
    ) {
        $this->framework = $framework;
        $this->session = $session;
        $this->memberUtil = $memberUtil;
        $this->modelUtil = $modelUtil;
        $this->fileUtil = $fileUtil;
    }

    public function getCurrentMemberGroups(): ?Collection
    {
        $member = $this->framework->getAdapter(FrontendUser::class)->getInstance();

        if (null === $member) {
            return new Collection([], 'tl_member_group');
        }

        return $this->memberUtil->getActiveGroups($member->id);
    }

    public function checkPermission(string $currentFolder, Model $fileManagerConfig): bool
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
        $groups = $this->getCurrentMemberGroups();

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
