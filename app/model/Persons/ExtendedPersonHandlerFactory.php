<?php

namespace Persons;

use Authentication\AccountManager;
use FKS\Config\GlobalParameters;
use Mail\MailTemplateFactory;
use ModelContest;
use Nette\Database\Connection;
use Nette\Object;
use ORM\IService;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ExtendedPersonHandlerFactory extends Object {

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

    function __construct(Connection $connection, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager, GlobalParameters $globalParameters) {
        $this->connection = $connection;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
        $this->globalParameters = $globalParameters;
    }

    public function create(IService $service, ModelContest $contest, $year) {
        $handler = new ExtendedPersonHandler($service, $this->connection, $this->mailTemplateFactory, $this->accountManager, $this->globalParameters);
        $handler->setContest($contest);
        $handler->setYear($year);
        return $handler;
    }

}
