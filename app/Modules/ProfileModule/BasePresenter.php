<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use Fykosak\Utils\UI\Title;

abstract class BasePresenter extends \FKSDB\Modules\Core\BasePresenter
{
    protected function getNavRoots(): array
    {
        return [
            [
                'title' => new Title(null, _('My profile')),
                'items' => [
                    'Profile:MyApplications:default' => [],
                    'Profile:MyPayments:default' => [],
                    'Profile:Email:default' => [],
                    'Profile:PostContact:default' => [],
                    'Profile:Lang:default' => [],
                    'Profile:Login:default' => [],
                    'Core:Settings:default' => [],
                ],
            ],
        ];
    }
}
