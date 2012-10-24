<?php

use Nette\ComponentModel\IContainer as IComponentContainer;
use Nette\Application\UI\Form;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class WizardCreateContestant extends WizardComponent {

    const STEP_FIND = 'find';
    const STEP_CREATE_PERSON = 'createPerson';
    const STEP_POST_CONTACTS = 'postContacts';
    const STEP_CREATE_CONTESTANT = 'createContestant';
    const STEP_CREATE_LOGIN = 'createLogin';
    const STEP_PERSON_INFO = 'personInfo';
    
    const SUBMIT_NEXT = 'next';
    const SUBMIT_FINISH = 'finish';

    public function __construct(IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $formFind = new FormPersonFind();
        $formFind->addSubmit(self::SUBMIT_NEXT, 'Pokračovat');
        $this->addStep($formFind, self::STEP_FIND, array($this, 'findSubmitted'));
        $this->registerStepSubmitter(self::STEP_FIND, self::SUBMIT_NEXT);

        $formPerson = new FormPerson();
        $formPerson->addSubmit(self::SUBMIT_NEXT, 'Pokračovat');
        $this->addStep($formPerson, self::STEP_CREATE_PERSON, self::STEP_POST_CONTACTS);
        $this->registerStepSubmitter(self::STEP_CREATE_PERSON, self::SUBMIT_NEXT);

        $formPostContacts = new FormPostContacts();
        $formPostContacts->addSubmit(self::SUBMIT_NEXT, 'Pokračovat');
        $this->addStep($formPostContacts, self::STEP_POST_CONTACTS, self::STEP_CREATE_CONTESTANT);
        $this->registerStepSubmitter(self::STEP_POST_CONTACTS, self::SUBMIT_NEXT);

        $formContestant = new FormContestant();
        $formContestant->addSubmit(self::SUBMIT_NEXT, 'Pokračovat');
        $formContestant->addSubmit(self::SUBMIT_FINISH, 'Dokončit'); //TODO handle this
        $this->addStep($formContestant, self::STEP_CREATE_CONTESTANT, array($this, 'contestantSubmitted'));
        $this->registerStepSubmitter(self::STEP_CREATE_CONTESTANT, self::SUBMIT_NEXT);
        $this->registerStepSubmitter(self::STEP_CREATE_CONTESTANT, self::SUBMIT_FINISH);
        $formContestant->loadSchools();     // must be called here (after attaching to a presenter)

        $formLogin = new FormLogin();
        $formLogin->addSubmit(self::SUBMIT_NEXT, 'Pokračovat');
        $this->addStep($formLogin, self::STEP_CREATE_LOGIN, self::STEP_PERSON_INFO);
        $this->registerStepSubmitter(self::STEP_CREATE_LOGIN, self::SUBMIT_NEXT);

        $formPersonInfo = new FormPersonInfo();
        $formPersonInfo->addSubmit(self::SUBMIT_FINISH, 'Dokončit');
        $this->addStep($formPersonInfo, self::STEP_PERSON_INFO);
        $this->registerStepSubmitter(self::STEP_PERSON_INFO, self::SUBMIT_NEXT);


        $this->setFirstStep(self::STEP_FIND);
        $this->onStepInit[] = array($this, 'initStep');
    }

    //   --- submit handlers ----
    public function findSubmitted(Form $form) {
        $values = $form->getValues();
        //TODO find results display again
        if ($values[FormPersonFind::ID_PERSON]) {
            return self::STEP_POST_CONTACTS;
        } else {
            return self::STEP_CREATE_PERSON;
        }
    }

    public function contestantSubmitted(Form $form) {
        if($form[self::SUBMIT_FINISH]->isSubmittedBy()){
            return null;
        }
        
        $values = $this->getData(self::STEP_FIND);
        if ($values[FormPersonFind::ID_PERSON]) {
            $loginService = $this->getPresenter()->getService('ServiceLogin');
            $login = $loginService->findByPrimary($values[FormPersonFind::ID_PERSON]);
            if ($login !== null) {
                return self::STEP_PERSON_INFO;
            } else {
                return self::STEP_CREATE_LOGIN;
            }
        } else {
            return self::STEP_CREATE_LOGIN;
        }
    }

    //   --- step initialization ---
    public function initStep(Form $form) {
        switch ($form->getName()) {
            case self::STEP_CREATE_PERSON:
                $this->initCreatePerson($form);
                break;
            case self::STEP_POST_CONTACTS:
                $this->initPostContact($form);
                break;
            case self::STEP_CREATE_CONTESTANT:
                $this->initCreateContestant($form);
                break;
            case self::STEP_CREATE_LOGIN:
                $this->initCreateLogin($form);
                break;
            case self::STEP_PERSON_INFO:
                $this->initPersonInfo($form);
                break;
        }
    }

    private function initCreatePerson(Form $form) {
        $values = $this->getData(self::STEP_FIND);
        $fullname = $values[FormPersonFind::FULLNAME];
        $form->setDefaults(array('display_name' => $fullname));
    }

    private function initPostContact(Form $form) {
        $form->setValues($this->getPerson()->toArray());
        //TODO
    }

    private function initCreateContestant(Form $form) {
        $form->setValues($this->getPerson()->toArray());
        //TODO update school year, class for current year
        //TODO predict from spamee
        $contestant = $this->getPerson()->getLastContestant($this->getPresenter()->getSelectedContest());
        if ($contestant !== null) {
            $form->setDefaults($contestant->toArray());
        }        
    }

    private function initCreateLogin(Form $form) {
        $form->setValues($this->getPerson()->toArray());
        //TODO intentionally empty?
    }

    private function initPersonInfo(Form $form) {
        $form->setValues($this->getPerson()->toArray());
        //TODO intentionally empty?
    }

    //   --- utilities ---

    /**
     * null|false|ModelPerson  cache
     */
    private $person = false;

    /**
     * @return ModelPerson|null
     */
    public function getPerson() {
        if ($this->person === false) {
            $values = $this->getData(self::STEP_FIND);
            if (!$values) {
                $this->person = null;
            }
            $servicePerson = $this->getPresenter()->getService('ServicePerson');
            if ($values[FormPersonFind::ID_PERSON]) {
                $this->person = $servicePerson->findByPrimary($values[FormPersonFind::ID_PERSON]);
            } else {
                $this->person = $servicePerson->createNew($this->getData(self::STEP_CREATE_PERSON));
            }
        }
        return $this->person;
    }

}
