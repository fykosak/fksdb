<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Controls\Person\ChangeEmailComponent;
use FKSDB\Components\Controls\PreferredLangFormComponent;
use FKSDB\Components\Controls\Stalking\Components\ContestantListComponent;
use FKSDB\Components\Controls\Stalking\Components\PaymentListComponent;
use FKSDB\Components\EntityForms\AddressFormComponent;
use FKSDB\Components\EntityForms\LoginFomComponent;
use FKSDB\Components\Grids\Application\Person\NewApplicationsGrid;
use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PostContactType;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\UI\PageTitle;

class ProfilePresenter extends BasePresenter
{
    /** @persistent */
    public ?string $id = null;

    private AccountManager $accountManager;

    public function injectAccountManager(AccountManager $accountManager): void
    {
        $this->accountManager = $accountManager;
    }

    protected function startup(): void
    {
        /** @var LoginModel $login */
        $login = $this->getUser()->getIdentity();
        if (!$login || !$login->person) {
            $this->redirect(':Core:Authentication:login');
        }
        parent::startup();
    }

    private function getIds(): array
    {
        return [
            'login',
            'email',
            'lang',
            'address.delivery',
            'address.permanent',
        ];
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('My profile'), 'fa fa-cogs');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Edit profile'), 'fa fa-cogs');
    }

    public function titleConfirm(): PageTitle
    {
        return new PageTitle(null, _('Confirm new email'), 'fa fa-cogs');
    }

    public function titleApplications(): PageTitle
    {
        return new PageTitle(null, _('My applications'), 'fa fa-calendar-alt');
    }

    public function titlePayments(): PageTitle
    {
        return new PageTitle(null, _('My payments'), 'fa fa-credit-card');
    }

    public function actionConfirm(): void
    {
        $logger = new MemoryLogger();
        $this->accountManager->handleChangeEmail($this->getLoggedPerson(), $logger);
        FlashMessageDump::dump($logger, $this);
    }

    public function renderDefault(): void
    {
        $this->template->login = $this->getUser()->getIdentity();
        $this->template->person = $this->getLoggedPerson();
    }

    public function renderEdit(): void
    {
        $this->template->ids = $this->getIds();
        $this->template->id = $this->id ?? $this->getIds()[0];
    }

    protected function createComponentDeliveryPostContactForm(): AddressFormComponent
    {
        return $this->createComponentPostContactForm(PostContactType::Delivery);
    }

    protected function createComponentPermanentPostContactForm(): AddressFormComponent
    {
        return $this->createComponentPostContactForm(PostContactType::Permanent);
    }

    private function createComponentPostContactForm(PostContactType $type): AddressFormComponent
    {
        return new AddressFormComponent($this->getContext(), $type, $this->getLoggedPerson());
    }

    protected function createComponentChangeEmail(): ChangeEmailComponent
    {
        return new ChangeEmailComponent($this->getContext(), $this->getLoggedPerson(), $this->getLang());
    }

    protected function createComponentPreferredLangForm(): PreferredLangFormComponent
    {
        return new PreferredLangFormComponent($this->getContext(), $this->getLoggedPerson());
    }

    protected function createComponentLoginForm(): LoginFomComponent
    {
        return new LoginFomComponent($this->getContext(), $this->getUser()->getIdentity());
    }

    protected function createComponentPaymentList(): PaymentListComponent
    {
        return new PaymentListComponent(
            $this->getContext(),
            $this->getLoggedPerson(),
            FieldLevelPermissionValue::Full
        );
    }

    protected function createComponentContestantList(): ContestantListComponent
    {
        return new ContestantListComponent(
            $this->getContext(),
            $this->getLoggedPerson(),
            FieldLevelPermissionValue::Full,
            false
        );
    }

    protected function createComponentMyPaymentGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'payment',
            $this->getLoggedPerson(),
            FieldLevelPermissionValue::Full,
            $this->getContext()
        );
    }

    protected function createComponentNewApplicationsGrid(): NewApplicationsGrid
    {
        return new NewApplicationsGrid($this->getContext());
    }

    protected function createComponentEventTeachersGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'fyziklani_team_teacher',
            $this->getLoggedPerson(),
            FieldLevelPermissionValue::Full,
            $this->getContext()
        );
    }

    protected function createComponentEventParticipantsGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'event_participant',
            $this->getLoggedPerson(),
            FieldLevelPermissionValue::Full,
            $this->getContext()
        );
    }

    protected function createComponentTeamMembersGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'fyziklani_team_member',
            $this->getLoggedPerson(),
            FieldLevelPermissionValue::Full,
            $this->getContext()
        );
    }
}
