<?php

namespace Persons;

use Authentication\AccountManager;
use FKSDB\Config\GlobalParameters;
use FKSDB\ORM\ModelContest;
use Mail\MailTemplateFactory;
use Nette\Database\Connection;
use ORM\IService;
use ServicePerson;

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

    function __construct(ServicePerson $servicePerson, Connection $connection, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager, GlobalParameters $globalParameters) {
        $this->servicePerson = $servicePerson;
        $this->connection = $connection;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
    }

    public function create(IService $service, ModelContest $contest, $year, $invitationLang) {
        $handler = new ExtendedPersonHandler($service, $this->servicePerson, $this->connection, $this->mailTemplateFactory, $this->accountManager);
        $handler->setContest($contest);
        $handler->setYear($year);
        $handler->setInvitationLang($invitationLang);
        return $handler;
    }
}
