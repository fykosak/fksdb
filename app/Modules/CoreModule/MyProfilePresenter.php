<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Controls\Person\ChangeEmailComponent;
use FKSDB\Components\EntityForms\AddressFormComponent;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PostContactType;
use FKSDB\Models\ORM\Services\PersonInfoService;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use Tracy\Debugger;

class MyProfilePresenter extends BasePresenter
{
    private PersonInfoService $personInfoService;

    public function injectPersonInfoService(PersonInfoService $personInfoService): void
    {
        $this->personInfoService = $personInfoService;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('My profile'), 'fa fa-cogs');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Update my profile'), 'fa fa-cogs');
    }

    public function titleChangeEmail(): PageTitle
    {
        return new PageTitle(null, _('Change mail'), 'fa fa-cogs');
    }

    public function actionChangeEmail(): void
    {
        if ($this->tokenAuthenticator->isAuthenticatedByToken(AuthTokenType::ChangeEmail)) {
            try {
                $info = $this->personInfoService->findByPrimary($this->getLoggedPerson()->person_id);
                $newEmail = $this->tokenAuthenticator->getTokenData();
                $this->personInfoService->storeModel(['email' => $this->tokenAuthenticator->getTokenData(),], $info);
                $this->flashMessage(_('Email has ben changed'), Message::LVL_SUCCESS);
                $this->tokenAuthenticator->disposeAuthToken();
                Debugger::log(
                    sprintf(
                        'person %d (%s) with old email "%s" changed to %s',
                        $this->getLoggedPerson()->person_id,
                        $this->getLoggedPerson()->getFullName(),
                        $this->getLoggedPerson()->getInfo()->email,
                        $newEmail
                    ),
                    'email-change'
                );
            } catch (\Throwable) {
                $this->flashMessage(_('Some error occurred! Please contact system admins.'), Message::LVL_ERROR);
            }
        }
    }

    public function renderDefault(): void
    {
        $this->template->person = $this->getLoggedPerson();
    }

    public function renderChangeEmail(): void
    {
        /** @var LoginModel $login */
        $login = $this->getUser()->getIdentity();
        $this->template->changeActive = $login && $login->getActiveTokens(AuthTokenType::ChangeEmail)->fetch();
    }

    protected function createComponentDeliveryPostContactForm(): AddressFormComponent
    {
        return $this->createComponentPostContactForm(PostContactType::tryFrom(PostContactType::DELIVERY));
    }

    protected function createComponentPermanentPostContactForm(): AddressFormComponent
    {
        return $this->createComponentPostContactForm(PostContactType::tryFrom(PostContactType::PERMANENT));
    }

    private function createComponentPostContactForm(PostContactType $type): AddressFormComponent
    {
        return new AddressFormComponent($this->getContext(), $type, $this->getLoggedPerson());
    }

    protected function createComponentChangeEmail(): ChangeEmailComponent
    {
        return new ChangeEmailComponent($this->getContext(), $this->getLoggedPerson(), $this->getLang());
    }
}
