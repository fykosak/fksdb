<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\MachineCode\MachineCodeException;
use FKSDB\Models\ORM\Services\UnsubscribedEmailService;
use Fykosak\Utils\UI\PageTitle;
use Fykosak\Utils\UI\Title;

final class UnsubscribePresenter extends BasePresenter
{
    private UnsubscribedEmailService $service;

    public function injectService(UnsubscribedEmailService $service): void
    {
        $this->service = $service;
    }

    public function requiresLogin(): bool
    {
        return false;
    }

    public function authorizedDefault(): bool
    {
        return true;
    }

    public function titleDefault(): Title
    {
        return new PageTitle(null, _('Success'));
    }

    /**
     * @throws MachineCodeException
     */
    public function actionDefault(): void
    {
        $token = $this->getParameter('token');
        $email = MachineCode::parseStringHash(
            $token,
            $this->getContext()->getParameters()['machineCode']['salt']['unsubscribe']
        );

        $emailHash = sha1(strtolower($email));

        $unsubscribeMailModel = $this->service->getTable()->where('email_hash', $emailHash)->fetch();
        if (!$unsubscribeMailModel) {
            $this->service->storeModel(['email_hash' => $emailHash]);
        }
    }
}
