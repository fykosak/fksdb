<?php

namespace PublicModule;

use BasePresenter as CoreBasePresenter;
use FKS\Components\Controls\FormControl;
use FKS\Components\Forms\Containers\ContainerWithOptions;
use FKS\Components\Forms\Controls\CaptchaBox;
use FKS\Components\Forms\Controls\ReferencedId;
use FKS\Config\Expressions\Helpers;
use FKSDB\Components\Forms\Factories\ReferencedPersonFactory;
use IContestPresenter;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use ModelPerson;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\InvalidStateException;
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
     * @var \ServicePerson
     */
    protected $servicePerson;

    /**
     * @var \SeriesCalculator
     */
    protected $seriesCalculator;

    public function injectSeriesCalculator(\SeriesCalculator $seriesCalculator) {
        $this->seriesCalculator = $seriesCalculator;
    }

    public function injectServiceContestant(ServiceContestant $serviceContestant) {
        $this->serviceContestant = $serviceContestant;
    }

    public function injectServicePerson(\ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    public function injectReferencedPersonFactory(ReferencedPersonFactory $referencedPersonFactory) {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    public function injectHandlerFactory(ExtendedPersonHandlerFactory $handlerFactory) {
        $this->handlerFactory = $handlerFactory;
    }

    public function injectContainer(Container $container) {
        $this->container = $container;
    }


    public function getSelectedContest() {
        return $this->contestId ? $this->serviceContest->findByPrimary($this->contestId) : null;
    }

    public function getSelectedYear() {
        return $this->year;
        // TODO
        //  return $this['contestNav']->getYear() + $this->yearCalculator->getForwardShift($this->getSelectedContest());
    }

    public function getSelectedAcademicYear() {
        if (!$this->getSelectedContest()) {
            throw new InvalidStateException("Cannot get acadamic year without selected contest.");
        }
        return $this->yearCalculator->getAcademicYear($this->getSelectedContest(), $this->getSelectedYear());
    }

    private function getPerson() {
        if (!$this->person) {

            if ($this->user->isLoggedIn()) {
                $this->person = $this->user->getIdentity()->getPerson();
            } elseif ($this->personId !== -1) {
                $this->person = $this->servicePerson->findByPrimary($this->personId);
            } else {
                $this->person = null;
            }
        }
        return $this->person;
    }

    public function actionDefault() {
        $this->redirect('contest');
    }

    public function actionContestant() {
        $person = $this->getPerson();

        if ($this->user->isLoggedIn()) {
            if (!$person) {
                $this->flashMessage(_('Uživatel musí být osobou, aby se mohl registrovat jako řešitel.'), self::FLASH_INFO);
                $this->redirect(':Authentication:login');
            }
        }

        if ($this->getSelectedContest() && $person) {
            $contestants = $person->getActiveContestants($this->yearCalculator);
            $contest = $this->getSelectedContest();
            $contestant = isset($contestants[$contest->contest_id]) ? $contestants[$contest->contest_id] : null;
            if ($contestant && $contestant->year == $this->getSelectedYear()) {
                // TODO FIXME persistent flash
                $this->flashMessage(sprintf(_('%s již řeší %s.'), $person->getFullname(), $contest->name), self::FLASH_INFO);
                $this->redirect(':Authentication:login');
            }
        }
    }

    public function titleContestant() {
        $this->setTitle(sprintf(_('%s – registrace řešitele (%s. ročník)'), $this->getSelectedContest()->name, $this->getSelectedYear()));
    }

    public function actionContest() {
        if ($this->contestId) {
            $this->changeAction('year');
        }
    }

    public function actionYear() {
        if ($this->year) {
            $this->changeAction('email');
        }
    }

    public function actionEmail() {
        if ($this->personId) {
            $this->changeAction('contestant');
        }
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
        $this->template->years[] = $this->yearCalculator->getCurrentYear($contest);
    }

    public function handleChangeContest($contestId) {
        $this->redirect('this', ['contestId' => $contestId,]);
    }

    public function handleChangeYear($year) {
        $this->redirect('this', ['year' => $year,]);
    }


    public function createComponentEmailForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addText('email', _('email'));
        $form->addSubmit('submit', _('Search'));
        $form->onSuccess[] = [$this, 'emailFormSucceeded'];
        return $form;
    }

    public function emailFormSucceeded(Form $form) {
        $values = $form->getValues();
        $person = $this->servicePerson->findByEmail($values->email);
        $login = $person->getLogin();
        if ($login) {
            $this->redirect(':Authentication:', ['loginId' => $login->login_id]);
        }
        $this->redirect('this');
    }

    public function renderContestant() {

        $person = $this->getPerson();
        /**
         * @var $contestantForm Form
         */
        $contestantForm = $this['contestantForm'];
        $referencedId = $contestantForm->getForm()->getComponent(ExtendedPersonHandler::CONT_AGGR)->getComponent(ExtendedPersonHandler::EL_PERSON);
        if ($person) {
            $referencedId->setDefaultValue($person);
        } else {
            $referencedId->setDefaultValue(ReferencedId::VALUE_PROMISE);
        }
    }

    private function getFieldsDefinition() {
        $contestId = $this->getSelectedContest()->contest_id;
        $contestName = $this->globalParameters['contestMapping'][$contestId];
        return Helpers::evalExpressionArray($this->globalParameters[$contestName]['registerContestant'], $this->container);
    }

    public function createComponentContestantForm() {
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
        $submit->onClick[] = function (SubmitButton $button) use ($that, $handler) {
            $form = $button->getForm();
            if ($result = $handler->handleForm($form, $that)) { // intentionally =
                /*
                 * Do not automatically log in user with existing logins for security reasons.
                 * (If someone was able to fill the form without conflicts, he might gain escalated privileges.)
                 */
                if (!$that->getPerson() && $result !== ExtendedPersonHandler::RESULT_OK_EXISTING_LOGIN) {
                    $login = $handler->getPerson()->getLogin();
                    $that->getUser()->login($login);
                }
                $this->redirect(':Dashboard:default');
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

    public function messageExists() {
        return _('Řešitel je již registrován.');
    }

    public function getSelectedSeries() {
        return null;
    }
}
