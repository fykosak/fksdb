<?php

namespace FKSDB\Models\Persons;

use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\ORM\Models\ModelContestYear;
use Fykosak\NetteORM\AbstractService;
use FKSDB\Models\ORM\Services\ServicePerson;
use Nette\Database\Connection;

class ExtendedPersonHandlerFactory
{

    private ServicePerson $servicePerson;
    private Connection $connection;
    private AccountManager $accountManager;

    public function __construct(ServicePerson $servicePerson, Connection $connection, AccountManager $accountManager)
    {
        $this->servicePerson = $servicePerson;
        $this->connection = $connection;
        $this->accountManager = $accountManager;
    }

    public function create(AbstractService $service, ModelContestYear $contestYear, string $invitationLang): ExtendedPersonHandler
    {
        return new ExtendedPersonHandler($service, $this->servicePerson, $this->connection, $this->accountManager, $contestYear, $invitationLang);
    }
}
