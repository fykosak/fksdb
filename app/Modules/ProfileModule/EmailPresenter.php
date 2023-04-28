<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Components\Controls\Person\Edit\ChangeEmailComponent;
use FKSDB\Models\Authentication\AccountManager;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\UI\PageTitle;

class EmailPresenter extends BasePresenter
{
    private AccountManager $accountManager;

    public function injectAccountManager(AccountManager $accountManager): void
    {
        $this->accountManager = $accountManager;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Change email address'), 'fa fa-envelope');
    }

    public function titleConfirm(): PageTitle
    {
        return new PageTitle(null, _('Confirm new email'), 'fa fa-cogs');
    }

    public function actionConfirm(): void
    {
        $logger = new MemoryLogger();
        $this->accountManager->handleChangeEmail($this->getLoggedPerson(), $logger);
        FlashMessageDump::dump($logger, $this);
    }

    protected function createComponentChangeEmailForm(): ChangeEmailComponent
    {
        return new ChangeEmailComponent($this->getContext(), $this->getLoggedPerson(), $this->getLang());
    }
}
