<?php

declare(strict_types=1);

namespace FKSDB\Modules\ShopModule;

use FKSDB\Components\Payments\AllPaymentList;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;
use Fykosak\Utils\UI\PageTitle;

final class AdminPresenter extends BasePresenter
{
    private ScheduleItemService $scheduleItemService;

    public function inject(ScheduleItemService $scheduleItemService): void
    {
        $this->scheduleItemService = $scheduleItemService;
    }

    public function getContest(): ContestModel
    {
        /** @var ContestModel $contest */
        $contest = $this->contestService->findByPrimary(ContestModel::ID_FYKOS);
        return $contest;
    }

    public function authorizedEvents(): bool
    {
        return $this->contestAuthorizator->isAllowed(
            PaymentModel::RESOURCE_ID,
            'dashboard',
            $this->getContest()
        );
    }

    public function titleEvents(): PageTitle
    {
        return new PageTitle(null, _('Payment dashboard'), 'fas fa-dashboard');
    }

    public function renderEvents(): void
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

    public function authorizedDefault(): bool
    {
        return $this->contestAuthorizator->isAllowed(
            PaymentModel::RESOURCE_ID,
            'list',
            $this->getContest()
        );
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('List of payments'), 'fas fa-credit-card');
    }

    protected function createComponentGrid(): AllPaymentList
    {
        return new AllPaymentList($this->getContext());
    }
}
