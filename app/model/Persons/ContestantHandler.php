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
use ServicePersonHistory;
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
     * @var ServicePersonHistory
     */
    private $servicePersonHistory;

    /**
     * @var ServiceMPostContact
     */
    private $serviceMPostContact;

    function __construct(ServicePersonHistory $servicePersonHistory, ServiceContestant $serviceContestant, ServiceMPostContact $serviceMPostContact, ServicePerson $servicePerson, ServicePersonInfo $servicePersonInfo, ServiceLogin $serviceLogin, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager) {
        parent::__construct($servicePerson, $servicePersonInfo, $serviceLogin, $mailTemplateFactory, $accountManager);

        $this->serviceContestant = $serviceContestant;
        $this->serviceMPostContact = $serviceMPostContact;
        $this->servicePersonHistory = $servicePersonHistory;
    }

    protected function storeExtendedData($data, Presenter $presenter) {
        /*
         * Contestant
         */
        $contestant = $this->serviceContestant->createNew();

        $contestant->person_id = $this->person->person_id;
        $contestant->contest_id = $presenter->getSelectedContest()->contest_id;
        $contestant->year = $presenter->getSelectedYear();

        $this->serviceContestant->save($contestant);

        /*
         * Person history
         */
        $dataHistory = $data[ExtendedPersonWizardFactory::CONT_PERSON_HISTORY];
        $dataHistory = FormUtils::emptyStrToNull($dataHistory);

        $personHistory = $this->servicePersonHistory->createNew($dataHistory);

        $personHistory->person_id = $this->person->person_id;
        $personHistory->ac_year = $presenter->getSelectedAcademicYear();

        $this->servicePersonHistory->save($personHistory);


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
