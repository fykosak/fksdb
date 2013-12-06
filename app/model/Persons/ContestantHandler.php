<?php

namespace Persons;

use Authentication\AccountManager;
use FKSDB\Components\Factories\ExtendedPersonWizardFactory;
use FormUtils;
use Mail\MailTemplateFactory;
use Nette\Application\UI\Presenter;
use ServiceContestant;
use ServiceLogin;
use ServiceMPostContact;
use ServicePerson;
use ServicePersonInfo;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ContestantHandler extends AbstractPersonHandler {

    /**
     * @var ServiceContestant
     */
    private $serviceContestant;

    /**
     * @var ServiceMPostContact
     */
    private $serviceMPostContact;

    function __construct(ServiceContestant $serviceContestant, ServiceMPostContact $serviceMPostContact, ServicePerson $servicePerson, ServicePersonInfo $servicePersonInfo, ServiceLogin $serviceLogin, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager) {
        parent::__construct($servicePerson, $servicePersonInfo, $serviceLogin, $mailTemplateFactory, $accountManager);

        $this->serviceContestant = $serviceContestant;
        $this->serviceMPostContact = $serviceMPostContact;
    }

    protected function storeExtendedData($data, Presenter $presenter) {
        /*
         * Contestant
         */
        $dataContestant = $data[ExtendedPersonWizardFactory::CONT_CONTESTANT];
        $dataContestant = FormUtils::emptyStrToNull($dataContestant);

        $contestant = $this->serviceContestant->createNew($dataContestant);

        $contestant->person_id = $this->person->person_id;
        $contestant->contest_id = $presenter->getSelectedContest()->contest_id;
        $contestant->year = $presenter->getSelectedYear();

        $this->serviceContestant->save($contestant);


        /*
         * Post contacts
         */
        foreach ($this->person->getMPostContacts() as $mPostContact) {
            $this->serviceMPostContact->dispose($mPostContact);
        }

        $dataPostContacts = $data[ExtendedPersonWizardFactory::CONT_ADDRESSES];
        foreach ($dataPostContacts as $dataPostContact) {
            $dataPostContact = FormUtils::emptyStrToNull((array) $dataPostContact);
            $mPostContact = $this->serviceMPostContact->createNew($dataPostContact);
            $mPostContact->getPostContact()->person_id = $this->person->person_id;

            $this->serviceMPostContact->save($mPostContact);
        }
    }

}
