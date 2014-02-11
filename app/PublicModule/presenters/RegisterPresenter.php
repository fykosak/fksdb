<?php

namespace PublicModule;

use BasePresenter as CoreBasePresenter;
use FKS\Components\Controls\FormControl;
use FKS\Components\Forms\Containers\ContainerWithOptions;
use FKS\Components\Forms\Controls\CaptchaBox;
use FKS\Components\Forms\Controls\ReferencedId;
use FKS\Config\Expressions\Helpers;
use FKSDB\Components\Controls\ContestChooser;
use FKSDB\Components\Forms\Factories\ReferencedPersonFactory;
use IContestPresenter;
use ModelContest;
use ModelPerson;
use Nette\Forms\Controls\SubmitButton;
use Persons\ExtendedPersonHandler;
use Persons\ExtendedPersonHandlerFactory;
use Persons\IExtendedPersonPresenter;
use Persons\SelfResolver;
use ServiceContestant;

/**
 * INPUT:
 *   contest (nullable)
 *   logged user (nullable)
 *   condition: the logged user is not contestant of the contest
 *   condition: the logged user is a person
 * 
 * OUTPUT:
 *   registered contestant for the current year
 *      - if contest was provided in that contest
 *      - if user was provided for that user
 * 
 * OPERATION
 *   - show/process person/login info iff logged user is null
 *   - show contest selector iff contest is null
 *   - contestant for filling default values
 *     - user must be logged in
 *     - if exists use last contestant from the provided contest
 *     - otherwise use last contestant from any contest (Vyfuk <= FYKOS)
 * 
 * Just proof of concept.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class RegisterPresenter extends CoreBasePresenter implements IContestPresenter, IExtendedPersonPresenter {

    /**
     * @var int
     * @persistent
     */
    public $contestId;

    /**
     * @var ModelPerson
     */
    private $person = false;

    /**
     * @var ServiceContestant
     */
    private $serviceContestant;

    /**
     * @var ReferencedPersonFactory
     */
    private $referencedPersonFactory;

    /**
     * @var ExtendedPersonHandlerFactory
     */
    private $handlerFactory;

    /** @var ModelContest|null */
    private $selectedContest;

    public function injectServiceContestant(ServiceContestant $serviceContestant) {
        $this->serviceContestant = $serviceContestant;
    }

    public function injectReferencedPersonFactory(ReferencedPersonFactory $referencedPersonFactory) {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    public function injectHandlerFactory(ExtendedPersonHandlerFactory $handlerFactory) {
        $this->handlerFactory = $handlerFactory;
    }

    protected function createComponentContestChooser($name) {
        $control = new ContestChooser($this->session, $this->yearCalculator, $this->serviceContest);
        $control->setContests(ContestChooser::ALL_CONTESTS);
        $control->setDefaultContest(ContestChooser::DEFAULT_NULL);
        return $control;
    }

    public function getSelectedContest() {
        return $this['contestChooser']->getContest();
        if ($this->selectedContest === null) {
            $this->selectedContest = $this->serviceContest->findByPrimary($this->contestId);
        }
        return $this->selectedContest;
    }

    public function getSelectedYear() {
        return $this['contestChooser']->getYear();
        return $this->yearCalculator->getCurrentYear($this->getSelectedContest());
    }

    public function getSelectedAcademicYear() {
        if (!$this->getSelectedContest()) {
            return $this->yearCalculator->getCurrentAcademicYear();
        }
        return $this->yearCalculator->getAcademicYear($this->getSelectedContest(), $this->getSelectedYear());
    }

    private function getPerson() {
        if ($this->person === false) {
            $this->person = $this->user->isLoggedIn() && $this->user->getIdentity()->getPerson() ? $this->user->getIdentity()->getPerson() : null;
        }
        return $this->person;
    }

    public function actionDefault() {
        // so far we do not implement registration of person only
        $this->redirect('contestant');
    }

    public function actionContestant() {
        if ($this->user->isLoggedIn()) {
            $person = $this->getPerson();
            if (!$person) {
                $this->flashMessage(_('Uživatel musí být osobou, aby se mohl registrovat jako řešitel.'), self::FLASH_INFO);
                $this->redirect(':Authentication:login');
            }

            if ($this->getSelectedContest()) {
                $contestnats = $person->getActiveContestants($this->yearCalculator);
                $contest = $this->getSelectedContest();
                if (isset($contestnats[$contest->contest_id])) {
                    $this->flashMessage(sprintf(_('%s již řeší %s.'), $person->getFullname(), $contest->name), self::FLASH_INFO);
                    $this->redirect(':Public:Dashboard:default');
                }
            }
        }
        if (!$this->getSelectedContest()) {
            $this->setView('contestChooser');
        }
    }

    public function titleContestant() {
        $this->setTitle(_('Registrace řešitele'));
    }

    public function renderContestant() {
        $person = $this->user->isLoggedIn() ? $this->user->getIdentity()->getPerson() : null;
        $referencedId = $this['contestantForm']->getForm()->getComponent(ExtendedPersonHandler::CONT_AGGR)->getComponent(ExtendedPersonHandler::EL_PERSON);
        if ($person) {
            $referencedId->setDefaultValue($person);
        } else {
            $referencedId->setDefaultValue(ReferencedId::VALUE_PROMISE);
        }
    }

    private function getFieldsDefinition() {
        $contestId = $this->getSelectedContest()->contest_id;
        $contestName = $this->globalParameters['contestMapping'][$contestId];
        return Helpers::evalExpressionArray($this->globalParameters[$contestName]['registerContestant']);
    }

    public function createComponentContestantForm($name) {
        $control = new FormControl();
        $form = $control->getForm();
        $control->setGroupMode(FormControl::GROUP_CONTAINER);

        $container = new ContainerWithOptions();
        $form->addComponent($container, ExtendedPersonHandler::CONT_AGGR);

        $fieldsDefinition = $this->getFieldsDefinition();
        $acYear = $this->getSelectedAcademicYear();
        $searchType = ReferencedPersonFactory::SEARCH_NONE;
        $allowClear = false;
        $modifiabilityResolver = $visibilityResolver = new SelfResolver($this->getUser());
        $components = $this->referencedPersonFactory->createReferencedPerson($fieldsDefinition, $acYear, $searchType, $allowClear, $modifiabilityResolver, $visibilityResolver);

        $container->addComponent($components[0], ExtendedPersonHandler::EL_PERSON);
        $container->addComponent($components[1], ExtendedPersonHandler::CONT_PERSON);


        /*
         * CAPTCHA
         */
        if (!$this->getPerson()) {
            $captcha = new CaptchaBox();
            $form->addComponent($captcha, 'captcha');
        }

        $handler = $this->handlerFactory->create($this->serviceContestant, $this->getSelectedContest(), $this->getSelectedYear(), $this->getLang());
        $submit = $form->addSubmit('register', _('Registrovat'));
        $that = $this;
        $submit->onClick[] = function(SubmitButton $button) use($that, $handler) {
                    $form = $button->getForm();
                    if ($handler->handleForm($form, $that)) {
                        if (!$that->getPerson()) {
                            $login = $handler->getPerson()->getLogin();
                            $that->getUser()->login($login);
                        }
                        $this->redirect(':Public:Dashboard:default');
                    }
                };


        $form->addProtection(_('Vypršela časová platnost formuláře. Odešlete jej prosím znovu.'));

        return $control;
    }

    public function getModel() {
        return null; //we always create new contestant
    }

    public function messageCreate() {
        return _('Řešitel %s zaregistrován.');
    }

    public function messageEdit() {
        return _('Řešitel %s upraven.');
    }

    public function messageError() {
        return _('Chyba při registraci.');
    }

}
