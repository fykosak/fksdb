<?php

namespace PublicModule;

use BasePresenter as CoreBasePresenter;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Controls\CaptchaBox;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Config\Expressions\Helpers;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServiceContestant;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\UI\PageStyleContainer;
use FKSDB\SeriesCalculator;
use IContestPresenter;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Controls\SubmitButton;
use Nette\InvalidStateException;
use Persons\ExtendedPersonHandler;
use Persons\ExtendedPersonHandlerFactory;
use Persons\IExtendedPersonPresenter;
use Persons\SelfResolver;

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
 * Just proof of concept (obsoleted due to ReferencedPerson).
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
     * @var int
     * @persistent
     */
    public $year;
    /**
     * @var int
     * @persistent
     */
    public $personId;

    /**
     * @var ModelPerson
     */
    private $person;

    private ServiceContestant $serviceContestant;

    private ReferencedPersonFactory $referencedPersonFactory;

    private ExtendedPersonHandlerFactory $handlerFactory;

    protected ServicePerson $servicePerson;

    protected SeriesCalculator $seriesCalculator;

    public function injectSeriesCalculator(SeriesCalculator $seriesCalculator): void {
        $this->seriesCalculator = $seriesCalculator;
    }

    public function injectServiceContestant(ServiceContestant $serviceContestant): void {
        $this->serviceContestant = $serviceContestant;
    }

    public function injectServicePerson(ServicePerson $servicePerson): void {
        $this->servicePerson = $servicePerson;
    }

    public function injectReferencedPersonFactory(ReferencedPersonFactory $referencedPersonFactory): void {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    public function injectHandlerFactory(ExtendedPersonHandlerFactory $handlerFactory): void {
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @return ModelContest|ActiveRow|null
     */
    public function getSelectedContest() {
        return $this->contestId ? $this->getServiceContest()->findByPrimary($this->contestId) : null;
    }

    /**
     * @return int
     */
    public function getSelectedYear() {
        return $this->year;
    }

    public function getSelectedAcademicYear(): int {
        if (!$this->getSelectedContest()) {
            throw new InvalidStateException("Cannot get acadamic year without selected contest.");
        }
        return $this->getYearCalculator()->getAcademicYear($this->getSelectedContest(), $this->getSelectedYear());
    }

    /**
     * @return ModelPerson|null
     */
    private function getPerson() {
        if (!isset($this->person)) {

            if ($this->user->isLoggedIn()) {
                $this->person = $this->user->getIdentity()->getPerson();
            } else {
                $this->person = null;
            }
        }
        return $this->person;
    }

    /**
     * @throws AbortException
     */
    public function actionDefault() {
        $this->redirect('contest');
    }

    /**
     * @throws AbortException
     */
    public function actionContestant() {

        if ($this->user->isLoggedIn()) {
            $person = $this->getPerson();

            if (!$person) {
                $this->flashMessage(_('Uživatel musí být osobou, aby se mohl registrovat jako řešitel.'), self::FLASH_INFO);
                $this->redirect(':Authentication:login');
            }
        } else {
            $email = $this->getHttpRequest()->getQuery('email');
            $person = $this->servicePerson->findByEmail($email);
            if ($person) {
                if ($person->getLogin()) {
                    $this->flashMessage('Byl nalezen existující účet, pro pokračování se přihlaste.');
                    $this->redirect(':Authentication:login', ['login' => $email, 'backlink' => $this->storeRequest()]);
                }
            }
        }

        if ($this->getSelectedContest() && $person) {
            $contestants = $person->getActiveContestants($this->getYearCalculator());
            $contest = $this->getSelectedContest();
            $contestant = isset($contestants[$contest->contest_id]) ? $contestants[$contest->contest_id] : null;
            if ($contestant && $contestant->year == $this->getSelectedYear()) {
                // TODO FIXME persistent flash
                $this->flashMessage(sprintf(_('%s již řeší %s.'), $person->getFullName(), $contest->name), self::FLASH_INFO);
                $this->redirect(':Authentication:login');
            }
        }
    }

    public function titleContestant() {
        $contest = $this->getSelectedContest();
        $this->setTitle(sprintf(_('%s – registrace řešitele (%s. ročník)'), $contest ? $contest->name : '', $this->getSelectedYear()));
    }

    public function actionContest() {
        if ($this->contestId) {
            $this->changeAction('year');
        }
    }

    public function titleContest() {
        $this->setTitle(_('Zvolit seminář'));
    }

    public function actionYear() {
        if ($this->year) {
            $this->changeAction('email');
        }
    }

    public function titleYear() {
        $this->setTitle(_('Zvolit ročník'), '', $this->getServiceContest()->findByPrimary($this->contestId)->name);
    }

    public function actionEmail() {

        if ($this->getParameter('email')) {
            $this->changeAction('contestant');
        }
    }

    public function titleEmail() {
        $this->setTitle(_('Zadejte e-mail'), 'fa fa-envelope', $this->getServiceContest()->findByPrimary($this->contestId)->name);
    }

    public function renderContest() {
        $pk = $this->getServiceContest()->getPrimary();

        $this->template->contests = array_map(function ($value) {
            return $this->getServiceContest()->findByPrimary($value);
        }, $this->getServiceContest()->fetchPairs($pk, $pk));
    }

    public function renderYear() {
        /** @var ModelContest $contest */
        $contest = $this->getServiceContest()->findByPrimary($this->contestId);
        $this->template->years = [];
        $this->template->years[] = $this->getYearCalculator()->getCurrentYear($contest) + $this->getYearCalculator()->getForwardShift($contest);
    }

    /**
     * @param $contestId
     * @throws AbortException
     */
    public function handleChangeContest($contestId) {
        $this->redirect('this', ['contestId' => $contestId,]);
    }

    /**
     * @param $year
     * @throws AbortException
     */
    public function handleChangeYear($year) {
        $this->redirect('this', ['year' => $year,]);
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    public function createComponentEmailForm() {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->addText('email', _('e-mail'));
        $form->addSubmit('submit', _('Vyhledat'));
        $form->onSuccess[] = function (Form $form) {
            $this->emailFormSucceeded($form);
        };
        return $control;
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    private function emailFormSucceeded(Form $form) {
        $values = $form->getValues();
        $this->redirect('this', ['email' => $values->email,]);
    }

    /**
     * @throws BadRequestException
     */
    public function renderContestant() {
        $person = $this->getPerson();
        /** @var FormControl $contestantForm */
        $contestantForm = $this->getComponent('contestantForm');
        /** @var ReferencedId $referencedId */
        $referencedId = $contestantForm->getForm()->getComponent(ExtendedPersonHandler::CONT_AGGR)->getComponent(ExtendedPersonHandler::EL_PERSON);
        if ($person) {
            $referencedId->setDefaultValue($person);
        } else {
            $referencedId->setDefaultValue(ReferencedId::VALUE_PROMISE);
        }
    }

    /**
     * @return array
     */
    private function getFieldsDefinition() {
        $contestId = $this->getSelectedContest()->contest_id;
        $contestName = $this->globalParameters['contestMapping'][$contestId];
        return Helpers::evalExpressionArray($this->globalParameters[$contestName]['registerContestant'], $this->getContext());
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws \Exception
     */
    public function createComponentContestantForm() {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();

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
        $submit->onClick[] = function (SubmitButton $button) use ($handler) {
            $form = $button->getForm();
            $result = $handler->handleForm($form, $this, true);
            if ($result) { // intentionally =
                /*
                 * Do not automatically log in user with existing logins for security reasons.
                 * (If someone was able to fill the form without conflicts, he might gain escalated privileges.)
                 */
                if (!$this->getPerson() && $result !== ExtendedPersonHandler::RESULT_OK_EXISTING_LOGIN) {
                    $login = $handler->getPerson()->getLogin();
                    $this->getUser()->login($login);
                }
                $this->redirect('Dashboard:default');

            }
        };
        $form->addProtection(_('Vypršela časová platnost formuláře. Odešlete jej prosím znovu.'));

        return $control;
    }

    /**
     * @return null|IModel
     */
    public function getModel() {
        return null; //we always create new contestant
    }

    public function messageCreate(): string {
        return _('Řešitel %s zaregistrován.');
    }

    public function messageEdit(): string {
        return _('Řešitel %s upraven.');
    }

    public function messageError(): string {
        return _('Chyba při registraci.');
    }

    public function messageExists(): string {
        return _('Řešitel je již registrován.');
    }

    /**
     * @return null
     */
    public function getSelectedSeries() {
        return null;
    }

    protected function getPageStyleContainer(): PageStyleContainer {
        $container = parent::getPageStyleContainer();
        $contest = $this->getSelectedContest();
        if ($contest) {
            $container->navBarClassName = 'bg-dark navbar-dark';
            $container->styleId = $contest->getContestSymbol();
        }
        return $container;
    }
}
