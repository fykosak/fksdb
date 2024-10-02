<?php

declare(strict_types=1);

namespace FKSDB\Modules\ShopModule;

use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\Payments\AllPaymentList;
use FKSDB\Components\Payments\PaymentList;
use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;
use Fykosak\Utils\UI\PageTitle;

final class AdminPresenter extends BasePresenter
{
    private PaymentService $paymentService;
    private ScheduleItemService $scheduleItemService;

    /** @persistent */
    public int $eventId;
    /** @persistent */
    public ?int $id;

    /**
     * @throws NotImplementedException
     */
    public function startup(): void
    {
        throw new NotImplementedException();
    }

    public function injectServices(
        PaymentService $paymentService,
        ScheduleItemService $scheduleItemService
    ): void {
        $this->paymentService = $paymentService;
        $this->scheduleItemService = $scheduleItemService;
    }

    public function authorizedEvents(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResourceId(PaymentModel::RESOURCE_ID, $this->getContest()),
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
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResourceId(PaymentModel::RESOURCE_ID, $this->getContest()),
            'list',
            $this->getContest()
        );
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('List of payments'), 'fas fa-credit-card');
    }

    /**
     * @throws NotFoundException
     */
    public function getPayment(): PaymentModel
    {
        $payment = $this->paymentService->findByPrimary($this->id);
        if (!$payment) {
            throw new NotFoundException();
        }
        return $payment;
    }

    protected function createComponentGrid(): AllPaymentList
    {
        return new AllPaymentList($this->getContext());
    }

    protected function createComponentGrid2(): PaymentList
    {
        return new PaymentList($this->getContext());
    }

    /**
     * @throws NotFoundException
     * @phpstan-return TransitionButtonsComponent<PaymentModel>
     */
    protected function createComponentButtonTransition(): TransitionButtonsComponent
    {
        return new TransitionButtonsComponent(
            $this->getContext(),
            $this->getMachine(), // @phpstan-ignore-line
            $this->getPayment()
        );
    }
}
