<?php

namespace Persons;

use Authentication\AccountManager;
use FKS\Config\GlobalParameters;
use Mail\MailTemplateFactory;
use Nette\ArrayHash;
use Nette\Database\Connection;
use Nette\Forms\Form;
use ServiceOrg;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class OrgHandler extends AbstractPersonHandler {

    /**
     * @var ServiceOrg
     */
    private $serviceOrg;

    function __construct(ServiceOrg $serviceOrg, Connection $connection, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager, GlobalParameters $globalParameters) {
        parent::__construct($connection, $mailTemplateFactory, $accountManager, $globalParameters);
        $this->serviceOrg = $serviceOrg;
    }

    protected function getReferencedPerson(Form $form) {
        //TODO   
    }

    protected function storeExtendedModel(ArrayHash $values) {
        //TODO
    }

}
