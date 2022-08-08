<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\ORM\Models\ContestYearModel;
use Fykosak\NetteORM\Service;
use FKSDB\Models\ORM\Services\PersonService;
use Nette\Database\Connection;

class ExtendedPersonHandlerFactory
{

    private PersonService $personService;
    private Connection $connection;
    private AccountManager $accountManager;

    public function __construct(PersonService $personService, Connection $connection, AccountManager $accountManager)
    {
        $this->personService = $personService;
        $this->connection = $connection;
        $this->accountManager = $accountManager;
    }

    public function create(
        Service $service,
        ContestYearModel $contestYear,
        string $invitationLang
    ): ExtendedPersonHandler {
        return new ExtendedPersonHandler(
            $service,
            $this->personService,
            $this->connection,
            $this->accountManager,
            $contestYear,
            $invitationLang
        );
    }
}
