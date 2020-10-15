<?php

namespace FKSDB\Modules\WarehouseModule;

use FKSDB\Exceptions\ContestNotFoundException;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\ORM\Models\ModelContest;
use Nette\Security\IResource;

/**
 * Class BasePresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class BasePresenter extends AuthenticatedPresenter {
    /**
     * @var int
     * @persistent
     */
    public $contestId;

    private ModelContest $contest;

    protected function getContest(): ModelContest {
        if (!isset($this->contest)) {
            $contest = $this->serviceContest->findByPrimary($this->contestId);
            if (is_null($contest)) {
                throw new ContestNotFoundException($this->contestId);
            }
            $this->contest = $contest;
        }
        return $this->contest;
    }

    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @return bool
     */
    protected function isAllowed($resource, ?string $privilege): bool {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getContest());
    }
}
