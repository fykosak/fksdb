<?php

declare(strict_types=1);

namespace FKSDB\Modules\PublicModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\EntityForms\RegisterContestantFormComponent;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\PersonModel;
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
class RegisterPresenter extends CoreBasePresenter
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
    private PersonService $personService;

    final public function injectTernary(PersonService $personService): void
    {
        $this->personService = $personService;
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

    public function actionEmail(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('contestant');
        }
    }

    public function actionContestant(): void
    {
        $loggedPerson = $this->getPerson();
        if (!$loggedPerson) {
            $email = $this->getHttpRequest()->getQuery('email');
            $loggedPerson = $this->personService->findByEmail($email);
            if ($loggedPerson && $loggedPerson->getLogin()) {
                $this->flashMessage(_('An existing account found. To continue, please sign in.'));
                $this->redirect(
                    ':Core:Authentication:login',
                    ['login' => $email, 'backlink' => $this->storeRequest()]
                );
            }
        }
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
        }
    }

    private function getPerson(): ?PersonModel
    {
        if (!$this->getUser()->isLoggedIn()) {
            return null;
        }
        return $this->getLoggedPerson();
    }

    final public function renderYear(): void
    {
        $contest = $this->getSelectedContest();
        $forwardedYear = $contest->getForwardedYear();
        if ($forwardedYear) {
            $years = [
                $contest->getCurrentContestYear(),
                $forwardedYear,
            ];
            $this->template->years = $years;
        } else {
            $this->redirect('email', ['year' => $contest->getCurrentContestYear()->year]);
        }
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

    protected function createComponentContestantForm(): RegisterContestantFormComponent
    {
        return new RegisterContestantFormComponent(
            $this->getContext(),
            $this->getLang(),
            $this->getSelectedContestYear(),
            $this->getPerson()
        );
    }

    public function getSelectedContestYear(): ?ContestYearModel
    {
        $contest = $this->getSelectedContest();
        if (is_null($contest)) {
            return null;
        }
        return $contest->getContestYear($this->year);
    }

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
