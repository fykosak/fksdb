<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Components\Controls\Person\ChangeEmailComponent;
use FKSDB\Components\Controls\PreferredLangFormComponent;
use FKSDB\Components\EntityForms\AddressFormComponent;
use FKSDB\Components\EntityForms\LoginFomComponent;
use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\ORM\Models\PostContactType;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\UI\PageTitle;

class EditPresenter extends AuthenticatedPresenter
{
    private AccountManager $accountManager;

    public function injectAccountManager(AccountManager $accountManager): void
    {
        $this->accountManager = $accountManager;
    }

    public function titleAddressDelivery(): PageTitle
    {
        return new PageTitle(id: null, title: _('Edit delivery address'), icon: 'fa fa-envelope');
    }

    public function titleConfirm(): PageTitle
    {
        return new PageTitle(id: null, title: _('Confirm new email'), icon: 'fa fa-cogs');
    }

    public function titleAddressPermanent(): PageTitle
    {
        return new PageTitle(id: null, title: _('Edit permanent address'), icon: 'fa fa-envelope');
    }

    public function titleEmail(): PageTitle
    {
        return new PageTitle(id: null, title: _('Change email address'), icon: 'fa fa-envelope');
    }

    public function titleLang(): PageTitle
    {
        return new PageTitle(id: null, title: _('Set preferred language'), icon: 'fa fa-language');
    }

    public function titleLogin(): PageTitle
    {
        return new PageTitle(id: null, title: _('Change login'), icon: 'fa fa-user');
    }

    public function actionConfirm(): void
    {
        $logger = new MemoryLogger();
        $this->accountManager->handleChangeEmail($this->getLoggedPerson(), $logger);
        FlashMessageDump::dump($logger, $this);
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
}
