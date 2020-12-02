<?php

namespace FKSDB\Modules\WarehouseModule;

use FKSDB\Exceptions\ContestNotFoundException;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\PresenterTraits\ContestPresenterTrait;
use FKSDB\ORM\Models\ModelContest;
use Nette\Security\IResource;

/**
 * Class BasePresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class BasePresenter extends AuthenticatedPresenter {
    use ContestPresenterTrait;

    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @return bool
     */
    protected function isAllowed($resource, ?string $privilege): bool {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }

    protected function getRole(): string {
        return 'org';
    }
}
