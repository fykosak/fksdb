<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Spam;

use FKSDB\Models\Authorization\Resource\ContestResource;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\Title;

abstract class BasePresenter extends \FKSDB\Modules\OrganizerModule\BasePresenter
{

    /**
     * @throws NoContestAvailable
     */
    protected function isAllowed(ContestResource $resource, ?string $privilege): bool
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
