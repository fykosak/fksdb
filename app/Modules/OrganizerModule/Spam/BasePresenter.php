<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Spam;

use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\Title;
use Nette\Security\Resource;

abstract class BasePresenter extends \FKSDB\Modules\OrganizerModule\BasePresenter
{

    /**
     * @param Resource|string|null $resource
     * @throws NoContestAvailable
     */
    protected function isAllowed($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }

    protected function getNavRoots(): array
    {
        return [
            [
                'title' => new Title(null, _('Persons')),
                'items' => [
                    'Spam:Person:list' => [],
                    'Spam:Person:create' => [],
                    'Spam:Person:import' => [],
                    'Spam:Mail:list' => [],
                    'Spam:Mail:import' => [],
                ]
            ],
            [
                'title' => new Title(null, _('Schools')),
                'items' => [
                    'Spam:School:list' => [],
                    'Spam:School:create' => [],
                ],
            ]
        ];
    }
}
