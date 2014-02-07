<?php

namespace Persons;

use Authentication\AccountManager;
use FKS\Config\GlobalParameters;
use Mail\MailTemplateFactory;
use ModelContest;
use Nette\ArrayHash;
use Nette\Database\Connection;
use Nette\Forms\Form;
use Nette\InvalidStateException;
use OrgModule\ContestantPresenter;
use ServiceContestant;

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
     * @var ModelContest
     */
    private $contest;

    /**
     * @var int
     */
    private $year;

    public function getContest() {
        return $this->contest;
    }

    public function setContest(ModelContest $contest) {
        $this->contest = $contest;
    }

    public function getYear() {
        return $this->year;
    }

    public function setYear($year) {
        $this->year = $year;
    }

    function __construct(ServiceContestant $serviceContestant, Connection $connection, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager, GlobalParameters $globalParameters) {
        parent::__construct($connection, $mailTemplateFactory, $accountManager, $globalParameters);
        $this->serviceContestant = $serviceContestant;
    }

    protected function getReferencedPerson(Form $form) {
        return $form[ContestantPresenter::CONT_MAIN][ContestantPresenter::EL_PERSON]->getModel();
    }

    protected function storeExtendedModel(ArrayHash $values) {
        if ($this->contest === null || $this->year === null) {
            throw new InvalidStateException('Must set contest and year before storing contestant.');
        }
        // initialize model
        $data = array(
            'contest_id' => $this->getContest()->contest_id,
            'year' => $this->getYear(),
        );
        $contestant = $this->serviceContestant->createNew($data);

        $contestant->person_id = $values[ContestantPresenter::CONT_MAIN][ContestantPresenter::EL_PERSON];

        // store model
        $this->serviceContestant->save($contestant);
    }

}
