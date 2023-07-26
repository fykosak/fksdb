<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\EntityForms\RegisterContestantFormComponent;
use FKSDB\Components\EntityForms\RegisterTeacherFormComponent;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Modules\Core\BasePresenter as CoreBasePresenter;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Form;

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
final class RegisterPresenter extends CoreBasePresenter
{
    /**
     * @persistent
     */
    public ?int $contestId = null;
    /**
     * @persistent
     */
    public ?int $year = null;

    private PersonService $personService;
    private EventService $eventService;

    final public function inject(PersonService $personService, EventService $eventService): void
    {
        $this->personService = $personService;
        $this->eventService = $eventService;
    }

    public function requiresLogin(): bool
    {
        return false;
    }

    public function authorizedDefault(): bool
    {
        return true;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Register'), 'fas fa-edit');
    }

    public function renderDefault(): void
    {
        $this->template->events = $this->eventService->getEventsWithOpenRegistration()
            ->order('registration_end')
            ->where('event_type_id', [1, 9, 2, 14]);
        $this->template->contests = $this->contestService->getTable();
    }

    public function authorizedYear(): bool
    {
        return true;
    }

    /**
     * @throws NotFoundException
     */
    public function titleYear(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Register to %s'), $this->getSelectedContest()->name),
            'fas fa-edit',
            _('Select year')
        );
    }

    /**
     * @throws NotFoundException
     */
    final public function renderYear(): void
    {
        $years = $this->getSelectedContest()->getActiveYears();
        if (count($years) === 1) {
            $this->redirect('email', ['year' => reset($years)->year]);
        } elseif (count($years) === 0) {
            $this->flashMessage(_('No year available'), Message::LVL_INFO);
        }
        $this->template->years = $years;
    }

    public function authorizedEmail(): bool
    {
        return true;
    }

    public function titleEmail(): PageTitle
    {
        return new PageTitle(null, _('Register'), 'fas fa-edit', _('Type e-mail'));
    }

    public function actionEmail(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('contestant');
        }
    }

    public function authorizedContestant(): bool
    {
        return true;
    }

    /**
     * @throws NotFoundException
     */
    public function titleContestant(): PageTitle
    {
        return new PageTitle(
            null,
            _('Register'),
            'fas fa-edit',
            sprintf(
                _('%s – contestant application (year %s)'),
                $this->getSelectedContest()->name,
                $this->getSelectedContestYear()->year
            )
        );
    }

    /**
     * @throws NotFoundException
     */
    public function actionContestant(): void
    {
        $loggedPerson = $this->getLoggedPerson();
        if ($loggedPerson) {
            $contestant = $loggedPerson->getContestantByContestYear($this->getSelectedContestYear());
            if ($contestant) {
                $this->flashMessage(
                    sprintf(
                        _('%s is already contestant in %s.'),
                        $loggedPerson->getFullName(),
                        $this->getSelectedContest()->name
                    ),
                    Message::LVL_INFO
                );
                $this->redirect(':Core:Authentication:login');
            }
        } else {
            $email = $this->getHttpRequest()->getQuery('email');
            $emailPerson = $this->personService->findByEmail($email);
            if ($emailPerson && $emailPerson->getLogin()) {
                $this->flashMessage(_('An existing account found. To continue, please sign in.'));
                $this->redirect(
                    ':Core:Authentication:login',
                    ['login' => $email, 'backlink' => $this->storeRequest()]
                );
            }
        }
    }

    public function authorizedTeacher(): bool
    {
        return true;
    }

    public function titleTeacher(): PageTitle
    {
        return new PageTitle(null, _('Register teacher'), 'fas fa-edit');
    }

    /**
     * @throws NotFoundException
     */
    public function getSelectedContest(): ContestModel
    {
        $contest = $this->contestService->findByPrimary($this->contestId);
        if (!$contest) {
            throw new NotFoundException(_('Contest not found!'));
        }
        return $contest;
    }

    /**
     * @throws NotFoundException
     */
    public function getSelectedContestYear(): ContestYearModel
    {
        return $this->getSelectedContest()->getContestYear($this->year);
    }

    protected function createComponentEmailForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->addText('email', _('E-mail'));
        $form->addSubmit('submit', _('Find'));
        $form->onSuccess[] = fn(Form $form) => $this->redirect('contestant', ['email' => $form->getValues()['email']]);
        return $control;
    }

    /**
     * @throws NotFoundException
     */
    protected function createComponentContestantForm(): RegisterContestantFormComponent
    {
        return new RegisterContestantFormComponent(
            $this->getContext(),
            $this->getLang(),
            $this->getSelectedContestYear(),
            $this->getLoggedPerson()
        );
    }

    protected function createComponentTeacherForm(): RegisterTeacherFormComponent
    {
        return new RegisterTeacherFormComponent(
            $this->getContext(),
            $this->getLang(),
            $this->getLoggedPerson()
        );
    }

    protected function getStyleId(): string
    {
        try {
            return 'contest-' . $this->getSelectedContest()->getContestSymbol();
        } catch (NotFoundException $exception) {
        }
        return parent::getStyleId();
    }
}
