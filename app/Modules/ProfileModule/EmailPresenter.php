<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Components\Controls\Person\Edit\ChangeEmailComponent;
use FKSDB\Components\Controls\Person\Edit\EmailPreferenceForm;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonInfoService;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;

final class EmailPresenter extends BasePresenter
{
    private PersonInfoService $personInfoService;

    public function injectAccountManager(PersonInfoService $personInfoService): void
    {
        $this->personInfoService = $personInfoService;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('E-mail settings'), 'fas fa-envelope');
    }

    public function titleConfirm(): PageTitle
    {
        return new PageTitle(null, _('Confirm new email'), 'fas fa-cogs');
    }

    public function authorizedDefault(): bool
    {
        return true;
    }

    public function authorizedConfirm(): bool
    {
        return true;
    }

    public function actionConfirm(): void
    {
        $logger = new MemoryLogger();
        $this->handleChangeEmail($this->getLoggedPerson(), $logger);
        FlashMessageDump::dump($logger, $this);
    }

    protected function createComponentChangeEmailForm(): ChangeEmailComponent
    {
        return new ChangeEmailComponent($this->getContext(), $this->getLoggedPerson());
    }

    protected function createComponentEmailPreferenceForm(): EmailPreferenceForm
    {
        return new EmailPreferenceForm($this->getContext(), $this->getLoggedPerson());
    }

    private function handleChangeEmail(PersonModel $person, Logger $logger): void
    {
        if (!$this->tokenAuthenticator->isAuthenticatedByToken(AuthTokenType::from(AuthTokenType::ChangeEmail))) {
            $logger->log(new Message(_('Invalid token'), Message::LVL_ERROR));
            // toto ma vypíčíť že nieje žiadny token na zmenu aktívny.
            //Možné príčiny: neskoro kliknutie na link; nebolo o zmenu vôbec požiuadané a nejak sa dostal sem.
            return;
        }
        try {
            $newEmail = $this->tokenAuthenticator->getTokenData();
            ChangeEmailComponent::logEmailChange($person, $newEmail, false);
            $this->personInfoService->storeModel([
                'email' => $newEmail,
            ], $person->getInfo());
            $logger->log(new Message(_('Email has been changed.'), Message::LVL_SUCCESS));
            $this->tokenAuthenticator->disposeAuthToken();
        } catch (\Throwable $exception) {
            $logger->log(new Message(_('Some error occurred! Please contact system admins.'), Message::LVL_ERROR));
        }
    }
}
