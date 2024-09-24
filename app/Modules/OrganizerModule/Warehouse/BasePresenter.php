<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Warehouse;

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
        return $this->authorizator->isAllowedContest($resource, $privilege, $this->getSelectedContest());
    }

    protected function getNavRoots(): array
    {
        $roots = parent::getNavRoots();
        $roots[] = [
            'title' => new Title(null, _('Applications')),
            'items' => [
                'Warehouse:Producer:list' => [],
                'Warehouse:Product:list' => [],
                'Warehouse:Item:list' => [],
            ],
        ];
        return $roots;
    }
}
