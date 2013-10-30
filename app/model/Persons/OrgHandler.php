<?php

namespace Persons;

use Authentication\AccountManager;
use FKSDB\Components\Factories\ExtendedPersonWizardFactory;
use FormUtils;
use Mail\MailTemplateFactory;
use Nette\Application\UI\Presenter;
use ServiceContestant;
use ServiceLogin;
use ServiceOrg;
use ServicePerson;
use ServicePersonInfo;

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

    function __construct(ServiceContestant $serviceContestant, ServicePerson $servicePerson, ServicePersonInfo $servicePersonInfo, ServiceLogin $serviceLogin, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager) {
        parent::__construct($servicePerson, $servicePersonInfo, $serviceLogin, $mailTemplateFactory, $accountManager);

        $this->serviceOrg = $serviceContestant;
    }

    protected function storeExtendedData($data, Presenter $presenter) {
        /*
         * Contestant
         */
        $dataOrg = $data[ExtendedPersonWizardFactory::CONT_ORG];
        $dataOrg = FormUtils::emptyStrToNull($dataOrg);

        $org = $this->serviceOrg->createNew($dataOrg);

        $org->person_id = $this->person->person_id;
        $org->contest_id = $presenter->getSelectedContest()->contest_id;

        $this->serviceOrg->save($org);
    }

}
