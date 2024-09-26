<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Payments\AllPaymentList;
use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;

final class PaymentsPresenter extends BasePresenter
{
    private ScheduleItemService $scheduleItemService;

    public function inject(ScheduleItemService $scheduleItemService): void
    {
        $this->scheduleItemService = $scheduleItemService;
    }

    protected function startup(): void
    {
        parent::startup();
        if ($this->getSelectedContest()->contest_id !== ContestModel::ID_FYKOS) {
            throw new ForbiddenRequestException();
        }
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedDashboard(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResourceId(PaymentModel::RESOURCE_ID, $this->getSelectedContest()),
            'dashboard',
            $this->getSelectedContest()
        );
    }

    public function titleDashboard(): PageTitle
    {
        return new PageTitle(null, _('Payment dashboard'), 'fas fa-dashboard');
    }

    public function renderDashboard(): void
    {
        $data = [];
        $paidCount = 0;
        $waitingCount = 0;
        $inProgressCount = 0;
        $noPaymentCount = 0;
        /** @var ScheduleItemModel $item */
        foreach ($this->scheduleItemService->getTable() as $item) {
            if ($item->payable) {
                /** @var PersonScheduleModel $personSchedule */
                foreach ($item->getInterested() as $personSchedule) {
                    $data[] = $personSchedule;
                    $payment = $personSchedule->getPayment();
                    if ($payment) {
                        switch ($payment->state->value) {
                            case PaymentState::RECEIVED:
                                $paidCount++;
                                break;
                            case PaymentState::WAITING:
                                $waitingCount++;
                                break;
                            case PaymentState::IN_PROGRESS:
                                $inProgressCount++;
                        }
                    } else {
                        $noPaymentCount++;
                    }
                }
            }
        }
        $this->template->paidCount = $paidCount;
        $this->template->waitingCount = $waitingCount;
        $this->template->noPaymentCount = $noPaymentCount;
        $this->template->inProgressCount = $inProgressCount;
        $this->template->rests = $data;
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResourceId(PaymentModel::RESOURCE_ID, $this->getSelectedContest()),
            'list',
            $this->getSelectedContest()
        );
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of payments'), 'fas fa-credit-card');
    }

    protected function createComponentGrid(): AllPaymentList
    {
        return new AllPaymentList($this->getContext());
    }
}
