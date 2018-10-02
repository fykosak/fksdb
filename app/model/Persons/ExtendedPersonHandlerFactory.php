<?php

namespace Persons;

use Authentication\AccountManager;
use FKSDB\Config\GlobalParameters;
use FKSDB\ORM\ModelContest;
use Mail\MailTemplateFactory;
use Nette\Database\Connection;
use Nette\Object;
use ORM\IService;
use ServicePerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ExtendedPersonHandlerFactory extends Object {

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
     * @var GlobalParameters
     */
    private $globalParameters;

    function __construct(ServicePerson $servicePerson, Connection $connection, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager, GlobalParameters $globalParameters) {
        $this->servicePerson = $servicePerson;
        $this->connection = $connection;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
        $this->globalParameters = $globalParameters;
    }

    public function create(IService $service, ModelContest $contest, $year, $invitationLang) {
        $handler = new ExtendedPersonHandler($service, $this->servicePerson, $this->connection, $this->mailTemplateFactory, $this->accountManager, $this->globalParameters);
        $handler->setContest($contest);
        $handler->setYear($year);
        $handler->setInvitationLang($invitationLang);
        return $handler;
    }

}
