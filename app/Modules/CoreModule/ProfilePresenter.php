<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Controls\Person\ChangeEmailComponent;
use FKSDB\Components\Controls\PreferredLangFormComponent;
use FKSDB\Components\EntityForms\AddressFormComponent;
use FKSDB\Components\EntityForms\LoginFomComponent;
use FKSDB\Components\Grids\Application\Person\NewApplicationsGrid;
use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PostContactType;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\UI\PageTitle;

class ProfilePresenter extends \FKSDB\Modules\CoreModule\BasePresenter
{
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


    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('My profile'), 'fa fa-cogs');
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
        $this->template->permanentAddress = $this->getLoggedPerson()->getAddress(PostContactType::Permanent);
        $this->template->deliveryAddress = $this->getLoggedPerson()->getAddress(PostContactType::Delivery);
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

    protected function createComponentMyPaymentGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'payment',
            $this->getLoggedPerson(),
            FieldLevelPermission::ALLOW_FULL,
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
            FieldLevelPermission::ALLOW_FULL,
            $this->getContext()
        );
    }

    protected function createComponentEventParticipantsGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'event_participant',
            $this->getLoggedPerson(),
            FieldLevelPermission::ALLOW_FULL,
            $this->getContext()
        );
    }

    protected function createComponentTeamMembersGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'fyziklani_team_member',
            $this->getLoggedPerson(),
            FieldLevelPermission::ALLOW_FULL,
            $this->getContext()
        );
    }
}
