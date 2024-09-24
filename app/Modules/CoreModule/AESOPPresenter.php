<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Models\Authorization\Resource\PseudoContestResource;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\WebService\AESOP\Models\ContestantModel;
use FKSDB\Models\WebService\AESOP\Models\EventParticipantModel;
use FKSDB\Models\WebService\AESOP\Models\TeacherEventModel;
use FKSDB\Models\WebService\AESOP\Models\TeamParticipantModel;
use FKSDB\Modules\Core\AuthMethod;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use FKSDB\Modules\Core\PresenterTraits\PresenterRole;
use FKSDB\Modules\Core\PresenterTraits\YearPresenterTrait;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

final class AESOPPresenter extends \FKSDB\Modules\Core\BasePresenter
{
    use YearPresenterTrait;

    public const AESOP_RESOURCE_ID = 'aesop';
    /**
     * @throws NoContestAvailable
     */
    public function authorizedContestant(): bool
    {
        return $this->authorizator->isAllowedContest(
            new PseudoContestResource(self::AESOP_RESOURCE_ID, $this->getSelectedContest()),
            null,
            $this->getSelectedContest()
        );
    }

    /**
     * @throws NotImplementedException
     */
    public function titleContestant(): PageTitle
    {
        throw new NotImplementedException();
    }
    /**
     * @throws NoContestAvailable
     */
    public function authorizedEvent(): bool
    {
        return $this->authorizator->isAllowedContest(
            new PseudoContestResource(self::AESOP_RESOURCE_ID, $this->getSelectedContest()),
            null,
            $this->getSelectedContest()
        );
    }

    /**
     * @throws NotImplementedException
     */
    public function titleEvent(): PageTitle
    {
        throw new NotImplementedException();
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

    /**
     * @throws NoContestYearAvailable
     * @throws NoContestAvailable
     */
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

    /**
     * @throws BadRequestException
     * @throws UnsupportedLanguageException
     * @throws ForbiddenRequestException
     */
    protected function startup(): void
    {
        parent::startup();
        $this->yearTraitStartup();
    }

    protected function getRole(): PresenterRole
    {
        return PresenterRole::from(PresenterRole::SELECTED);
    }
}
