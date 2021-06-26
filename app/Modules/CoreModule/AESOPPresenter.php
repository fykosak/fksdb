<?php

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Controls\Choosers\YearChooserComponent;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\WebService\AESOP\Models\ContestantModel;
use FKSDB\Models\WebService\AESOP\Models\EventParticipantModel;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\PresenterTraits\YearPresenterTrait;

class AESOPPresenter extends AuthenticatedPresenter {

    use YearPresenterTrait;

    private ServiceEvent $serviceEvent;

    private const XSLT_FILE = '';

    public function injectSecondary(ServiceEvent $serviceEvent): void {
        $this->serviceEvent = $serviceEvent;
    }

    protected function startup(): void {
        parent::startup();
        $this->yearTraitStartup();
    }

    public function authorizedContestant(): void {
        $this->contestAuthorizator->isAllowed('aesop', null, $this->getSelectedContest());
    }

    public function authorizedEvent(): void {
        $this->contestAuthorizator->isAllowed('aesop', null, $this->getSelectedContest());
    }

    public function renderContestant(): void {
        $category = $this->getParameter('category');
        $this->sendResponse((new ContestantModel($this->getContext(), $this->getSelectedContestYear(), $category))->createResponse());
    }

    public function renderEvent(): void {
        $eventName = $this->getParameter('eventName');
        $type = $this->getParameter('type');
        if (is_null($type)) {
            $this->sendResponse((new EventParticipantModel($this->getContext(), $this->getSelectedContestYear(), $eventName))->createResponse());
        }
    }

    public function getAllowedAuthMethods(): array {
        return [
            self::AUTH_GITHUB => false,
            self::AUTH_HTTP => true,
            self::AUTH_LOGIN => true,
            self::AUTH_TOKEN => true,
        ];
    }

    protected function getRole(): string {
        return YearChooserComponent::ROLE_SELECTED;
    }
}
