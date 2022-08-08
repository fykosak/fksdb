<?php

declare(strict_types=1);

namespace FKSDB\Modules\PublicModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\CaptchaBox;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Expressions\Helpers;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\ContestantService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\Persons\ExtendedPersonHandler;
use FKSDB\Models\Persons\ExtendedPersonHandlerFactory;
use FKSDB\Models\Persons\ExtendedPersonPresenter;
use FKSDB\Models\Persons\SelfResolver;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\BasePresenter as CoreBasePresenter;
use Fykosak\NetteORM\Model;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;

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
 */
class RegisterPresenter extends CoreBasePresenter implements ExtendedPersonPresenter
{

    /**
     * @persistent
     */
    public ?int $contestId = null;
    /**
     * @persistent
     */
    public ?int $year = null;
    /**
     * @persistent
     */
    public ?int $personId = null;
    private ?PersonModel $person;
    private ContestantService $contestantService;
    private ReferencedPersonFactory $referencedPersonFactory;
    private ExtendedPersonHandlerFactory $handlerFactory;
    private PersonService $personService;

    final public function injectTernary(
        ContestantService $contestantService,
        PersonService $personService,
        ReferencedPersonFactory $referencedPersonFactory,
        ExtendedPersonHandlerFactory $handlerFactory
    ): void {
        $this->contestantService = $contestantService;
        $this->personService = $personService;
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->handlerFactory = $handlerFactory;
    }

    /* ********************* TITLE ***************** */
    public function titleContest(): PageTitle
    {
        return new PageTitle(null, _('Register'), 'fa fa-edit', _('Select contest'));
    }

    public function titleYear(): PageTitle
    {
        return new PageTitle(null, _('Register'), 'fa fa-edit', _('Select year'));
    }

    public function titleEmail(): PageTitle
    {
        return new PageTitle(null, _('Register'), 'fa fa-edit', _('Type e-mail'));
    }

    public function titleContestant(): PageTitle
    {
        return new PageTitle(
            null,
            _('Register'),
            'fa fa-edit',
            sprintf(
                _('%s – contestant application (year %s)'),
                $this->getSelectedContest()->name,
                $this->getSelectedYear()
            )
        );
    }

    public function getSelectedContest(): ?ContestModel
    {
        return $this->contestId ? $this->contestService->findByPrimary($this->contestId) : null;
    }

    /* ********************* ACTIONS ***************** */


    public function getSelectedYear(): ?int
    {
        return $this->year;
    }

    public function actionDefault(): void
    {
        $this->redirect('contest');
    }

    public function actionContestant(): void
    {
        if ($this->user->isLoggedIn()) {
            $person = $this->person;

            if (!$person) {
                $this->flashMessage(
                    _('User must be a person in order to register as a contestant.'),
                    Message::LVL_INFO
                );
                $this->redirect(':Core:Authentication:login');
            }
        } else {
            $email = $this->getHttpRequest()->getQuery('email');
            $person = $this->personService->findByEmail($email);
            if ($person) {
                if ($person->getLogin()) {
                    $this->flashMessage(_('An existing account found. To continue, please sign in.'));
                    $this->redirect(
                        ':Core:Authentication:login',
                        ['login' => $email, 'backlink' => $this->storeRequest()]
                    );
                }
            }
        }

        if ($this->getSelectedContest() && $person) {
            $contestants = $person->getActiveContestants();
            $contest = $this->getSelectedContest();
            $contestant = $contestants[$contest->contest_id] ?? null;
            if ($contestant && $contestant->year == $this->getSelectedYear()) {
                $this->flashMessage(
                    sprintf(_('%s is already contestant in %s.'), $person->getFullName(), $contest->name),
                    Message::LVL_INFO
                );
                $this->redirect(':Core:Authentication:login');
            }
        }
    }

    private function getPerson(): ?PersonModel
    {
        if (!isset($this->person)) {
            if ($this->user->isLoggedIn()) {
                $this->person = $this->user->getIdentity()->person;
            } else {
                $this->person = null;
            }
        }
        return $this->person;
    }

    final public function renderContest(): void
    {
        $this->template->contests = $this->contestService->getTable();
    }

    final public function renderYear(): void
    {
        $contest = $this->getSelectedContest();
        $forward = $this->yearCalculator->getForwardShift($contest);
        if ($forward) {
            $years = [
                $contest->getCurrentContestYear()->year,
                $contest->getCurrentContestYear()->year + $forward,
            ];

            $this->template->years = $years;
        } else {
            $this->redirect('email', ['year' => $contest->getCurrentContestYear()->year,]);
        }
    }

    /**
     * @throws BadTypeException
     */
    final public function renderContestant(): void
    {
        $person = $this->getPerson();
        /** @var FormControl $contestantForm */
        $contestantForm = $this->getComponent('contestantForm');
        /** @var ReferencedId $referencedId */
        $referencedId = $contestantForm->getForm()->getComponent(ExtendedPersonHandler::CONT_AGGR)->getComponent(
            ExtendedPersonHandler::EL_PERSON
        );
        $referencedId->setDefaultValue($person ?? ReferencedId::VALUE_PROMISE);
    }

    public function getModel(): ?Model
    {
        return null; //we always create new contestant
    }

    public function messageCreate(): string
    {
        return _('Contestant %s registered.');
    }

    public function messageEdit(): string
    {
        return _('Contestant %s modified.');
    }

    public function messageError(): string
    {
        return _('Error while registering.');
    }

    public function messageExists(): string
    {
        return _('Contestant already registered.');
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentEmailForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->addText('email', _('E-mail'));
        $form->addSubmit('submit', _('Find'));
        $form->onSuccess[] = fn(Form $form) => $this->emailFormSucceeded($form);
        return $control;
    }

    private function emailFormSucceeded(Form $form): void
    {
        $values = $form->getValues();
        $this->redirect('contestant', ['email' => $values['email'],]);
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function createComponentContestantForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();

        $container = new ContainerWithOptions();
        $form->addComponent($container, ExtendedPersonHandler::CONT_AGGR);
        $referencedId = $this->referencedPersonFactory->createReferencedPerson(
            $this->getFieldsDefinition(),
            $this->getSelectedContestYear(),
            PersonSearchContainer::SEARCH_NONE,
            false,
            new SelfResolver($this->getUser()),
            new SelfResolver($this->getUser())
        );

        $container->addComponent($referencedId, ExtendedPersonHandler::EL_PERSON);

        /*
         * CAPTCHA
         */
        if (!$this->getPerson()) {
            $captcha = new CaptchaBox();
            $form->addComponent($captcha, 'captcha');
        }

        $handler = $this->handlerFactory->create(
            $this->contestantService,
            $this->getSelectedContestYear(),
            $this->getLang()
        );

        $submit = $form->addSubmit('register', _('Register'));
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
        $form->addProtection(_('The form has expired. Please send it again.'));

        return $control;
    }

    /**
     * @throws \ReflectionException
     */
    private function getFieldsDefinition(): array
    {
        $contestName = $this->getSelectedContest()->getContestSymbol();
        return Helpers::evalExpressionArray(
            $this->getContext()->getParameters()[$contestName]['registerContestant'],
            $this->getContext()
        );
    }

    public function getSelectedContestYear(): ?ContestYearModel
    {
        $contest = $this->getSelectedContest();
        if (is_null($contest)) {
            return null;
        }
        $row = $contest->getContestYears()->where('year', $this->year)->fetch();
        return $row ? ContestYearModel::createFromActiveRow($row) : null;
    }

    /**
     * @throws BadTypeException
     * @throws BadRequestException
     * @throws \ReflectionException
     */
    protected function beforeRender(): void
    {
        $contest = $this->getSelectedContest();
        if ($contest) {
            $this->getPageStyleContainer()->setNavBarClassName('bg-dark navbar-dark');
            $this->getPageStyleContainer()->setNavBrandPath('/images/logo/white.svg');
            $this->getPageStyleContainer()->styleIds[] = $contest->getContestSymbol();
        }
        parent::beforeRender();
    }
}
