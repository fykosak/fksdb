<?php

namespace FKSDB\Persons;

use FKSDB\Authentication\AccountManager;
use FKSDB\ORM\IService;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Services\ServicePerson;
use Nette\Database\Connection;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ExtendedPersonHandlerFactory {

    private ServicePerson $servicePerson;

    private Connection $connection;

    private AccountManager $accountManager;

    public function __construct(ServicePerson $servicePerson, Connection $connection, AccountManager $accountManager) {
        $this->servicePerson = $servicePerson;
        $this->connection = $connection;
        $this->accountManager = $accountManager;
    }

    public function create(IService $service, ModelContest $contest, int $year, string $invitationLang): ExtendedPersonHandler {
        return new ExtendedPersonHandler($service, $this->servicePerson, $this->connection, $this->accountManager, $contest, $year, $invitationLang);
    }
}
