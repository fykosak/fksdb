<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Models\WebService\AESOP\Models\ContestantModel;
use FKSDB\Models\WebService\AESOP\Models\EventParticipantModel;
use FKSDB\Models\WebService\AESOP\Models\TeacherEventModel;
use FKSDB\Models\WebService\AESOP\Models\TeamParticipantModel;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\AuthMethod;
use FKSDB\Modules\Core\PresenterTraits\PresenterRole;
use FKSDB\Modules\Core\PresenterTraits\YearPresenterTrait;
use Nette\Application\BadRequestException;

class AESOPPresenter extends AuthenticatedPresenter
{
    use YearPresenterTrait;

    public function authorizedContestant(): bool
    {
        return $this->contestAuthorizator->isAllowed('aesop', null, $this->getSelectedContest());
    }

    public function authorizedEvent(): bool
    {
        return $this->contestAuthorizator->isAllowed('aesop', null, $this->getSelectedContest());
    }

    /**
     * @throws BadRequestException
     */
    public function renderContestant(): void
    {
        $category = $this->getParameter('category');
        $this->sendResponse(
            (new ContestantModel($this->getContext(), $this->getSelectedContestYear(), $category))->createResponse()
        );
    }

    public function renderEvent(): void
    {
        $eventName = $this->getParameter('eventName');
        $type = $this->getParameter('type');
        if (is_null($type)) {
            $model = new EventParticipantModel($this->getContext(), $this->getSelectedContestYear(), $eventName);
        } elseif ($type === 'uc') {
            $model = new TeacherEventModel($this->getContext(), $this->getSelectedContestYear(), $eventName);
        } else {
            $model = new TeamParticipantModel($this->getContext(), $this->getSelectedContestYear(), $eventName, $type);
        }
        $this->sendResponse($model->createResponse());
    }

    public function isAuthAllowed(AuthMethod $authMethod): bool
    {
        switch ($authMethod->value) {
            case AuthMethod::LOGIN:
            case AuthMethod::HTTP:
                return true;
            case AuthMethod::TOKEN:
                return false;
        }
        return false;
    }

    protected function getHttpRealm(): ?string
    {
        return 'AESOP';
    }

    protected function startup(): void
    {
        parent::startup();
        $this->yearTraitStartup();
    }

    protected function getRole(): PresenterRole
    {
        return PresenterRole::tryFrom(PresenterRole::SELECTED);
    }
}
