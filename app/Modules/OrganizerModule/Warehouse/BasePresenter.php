<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Warehouse;

use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\Title;
use Nette\Security\Resource;

abstract class BasePresenter extends \FKSDB\Modules\OrganizerModule\BasePresenter
{

    /**
     * @param Resource|string|null $resource
     * @throws NoContestAvailable
     * @throws NoContestAvailable
     */
    protected function isAllowed($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
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
