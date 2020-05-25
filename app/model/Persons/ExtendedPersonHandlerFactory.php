<?php

namespace Persons;

use FKSDB\Authentication\AccountManager;
use FKSDB\Config\GlobalParameters;
use FKSDB\ORM\IService;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Services\ServicePerson;
use Mail\MailTemplateFactory;
use Nette\Database\Connection;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ExtendedPersonHandlerFactory {

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var MailTemplateFactory
     */
    private $mailTemplateFactory;

    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * ExtendedPersonHandlerFactory constructor.
     * @param ServicePerson $servicePerson
     * @param Connection $connection
     * @param MailTemplateFactory $mailTemplateFactory
     * @param AccountManager $accountManager
     * @param GlobalParameters $globalParameters
     */
    public function __construct(ServicePerson $servicePerson, Connection $connection, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager, GlobalParameters $globalParameters) {
        $this->servicePerson = $servicePerson;
        $this->connection = $connection;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
    }

    public function create(IService $service, ModelContest $contest, int $year, string $invitationLang): ExtendedPersonHandler {
        $handler = new ExtendedPersonHandler($service, $this->servicePerson, $this->connection, $this->mailTemplateFactory, $this->accountManager);
        $handler->setContest($contest);
        $handler->setYear($year);
        $handler->setInvitationLang($invitationLang);
        return $handler;
    }
}
