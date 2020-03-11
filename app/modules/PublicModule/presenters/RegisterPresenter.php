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
use FKSDB\SeriesCalculator;
use IContestPresenter;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container;
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
     * @var integer
     * @persistent
     */
    public $contestId;
    /**
     * @var integer
     * @persistent
     */
    public $year;
    /**
     * @var integer
     * @persistent
     */
    public $personId;

    /**
     * @var ModelPerson
     */
    private $person;

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

    /**
     * @var Container
     */
    private $container;
    /**
     * @var ServicePerson
     */
    protected $servicePerson;

    /**
     * @var SeriesCalculator
     */
    protected $seriesCalculator;

    /**
     * @param SeriesCalculator $seriesCalculator
     */
    public function injectSeriesCalculator(SeriesCalculator $seriesCalculator) {
        $this->seriesCalculator = $seriesCalculator;
    }

    /**
     * @param ServiceContestant $serviceContestant
     */
    public function injectServiceContestant(ServiceContestant $serviceContestant) {
        $this->serviceContestant = $serviceContestant;
    }

    /**
     * @param ServicePerson $servicePerson
     */
    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param ReferencedPersonFactory $referencedPersonFactory
     */
    public function injectReferencedPersonFactory(ReferencedPersonFactory $referencedPersonFactory) {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    /**
     * @param ExtendedPersonHandlerFactory $handlerFactory
     */
    public function injectHandlerFactory(ExtendedPersonHandlerFactory $handlerFactory) {
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @param Container $container
     */
    public function injectContainer(Container $container) {
        $this->container = $container;
    }


    /**
     * @return ModelContest|ActiveRow|null
     */
    public function getSelectedContest() {
        return $this->contestId ? $this->serviceContest->findByPrimary($this->contestId) : null;
    }

    /**
     * @return int
     */
    public function getSelectedYear() {
        return $this->year;
    }

    /**
     * @return int|mixed
     */
    public function getSelectedAcademicYear() {
        if (!$this->getSelectedContest()) {
            throw new InvalidStateException("Cannot get acadamic year without selected contest.");
        }
        return $this->yearCalculator->getAcademicYear($this->getSelectedContest(), $this->getSelectedYear());
    }

    /**
     * @return ModelPerson|null
     */
    private function getPerson() {
        if (!$this->person) {

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
            $contestants = $person->getActiveContestants($this->yearCalculator);
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
        $this->setSubtitle($this->serviceContest->findByPrimary($this->contestId)->name);
        $this->setTitle(_('Zvolit ročník'));
    }

    public function actionEmail() {

        if ($this->getParameter('email')) {
            $this->changeAction('contestant');
        }
    }

    public function titleEmail() {
        $this->setSubtitle($this->serviceContest->findByPrimary($this->contestId)->name);
        $this->setTitle(_('Zadejte e-mail'));
    }

    public function renderContest() {
        $pk = $this->serviceContest->getPrimary();

        $this->template->contests = array_map(function ($value) {
            return $this->serviceContest->findByPrimary($value);
        }, $this->serviceContest->fetchPairs($pk, $pk));
    }

    public function renderYear() {
        $contest = $this->serviceContest->findByPrimary($this->contestId);
        $this->template->years = [];
        $this->template->years[] = $this->yearCalculator->getCurrentYear($contest) + $this->yearCalculator->getForwardShift($contest);
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
        $control = new FormControl();
        $form = $control->getForm();
        // $form = new Form();
        // $form->setRenderer(new BootstrapRenderer());
        $form->addText('email', _('e-mail'));
        $form->addSubmit('submit', _('Vyhledat'));
        $form->onSuccess[] = [$this, 'emailFormSucceeded'];
        return $control;
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    public function emailFormSucceeded(Form $form) {
        $values = $form->getValues();

        $this->redirect('this', ['email' => $values->email,]);
    }

    public function renderContestant() {

        $person = $this->getPerson();
        /**
         * @var Form $contestantForm
         */
        $contestantForm = $this->getComponent('contestantForm');
        $referencedId = $contestantForm->getForm()->getComponent(ExtendedPersonHandler::CONT_AGGR)->getComponent(ExtendedPersonHandler::EL_PERSON);
        if ($person) {
            $referencedId->setDefaultValue($person);
        } else {
            $referencedId->setDefaultValue(ReferencedId::VALUE_PROMISE);
        }
    }

    /**
     * @return array|mixed
     */
    private function getFieldsDefinition() {
        $contestId = $this->getSelectedContest()->contest_id;
        $contestName = $this->globalParameters['contestMapping'][$contestId];
        return Helpers::evalExpressionArray($this->globalParameters[$contestName]['registerContestant'], $this->container);
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    public function createComponentContestantForm() {
        $control = new FormControl();
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
        $form->onSuccess[] = function (Form $form) use ($handler) {
            if ($result = $handler->handleForm($form, $this, true)) { // intentionally =
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

    /**
     * @return string
     */
    public function messageCreate() {
        return _('Řešitel %s zaregistrován.');
    }

    /**
     * @return string
     */
    public function messageEdit() {
        return _('Řešitel %s upraven.');
    }

    /**
     * @return string
     */
    public function messageError() {
        return _('Chyba při registraci.');
    }

    /**
     * @return string
     */
    public function messageExists() {
        return _('Řešitel je již registrován.');
    }

    /**
     * @return null
     */
    public function getSelectedSeries() {
        return null;
    }

    /**
     * @return array
     */
    protected function getNavBarVariant(): array {
        /**
         * @var ModelContest $contest
         */
        $contest = $this->serviceContest->findByPrimary($this->contestId);
        if ($contest) {
            return [$contest->getContestSymbol(), 'bg-dark navbar-dark'];
        }
        return parent::getNavBarVariant();
    }
}
