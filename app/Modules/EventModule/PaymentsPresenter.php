<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\EntityForms\PaymentForm;
use FKSDB\Components\Grids\Payment\PaymentList;
use FKSDB\Components\Grids\Payment\PaymentQRCode;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Security\Resource;
use Nette\Utils\Html;

final class PaymentsPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<PaymentModel> */
    use EntityPresenterTrait;

    private PaymentService $paymentService;

    public function inject(PaymentService $paymentService): void
    {
        $this->paymentService = $paymentService;
    }

    public function authorizedCreate(): bool
    {
        $event = $this->getEvent();
        return $this->eventAuthorizator->isAllowed(PaymentModel::RESOURCE_ID, 'organizer', $event)
            || ($this->isPaymentAllowed() &&
                $this->eventAuthorizator->isAllowed(PaymentModel::RESOURCE_ID, 'create', $event));
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create payment'), 'fas fa-credit-card');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDashboard(): bool
    {
        return $this->eventAuthorizator->isAllowed(PaymentModel::RESOURCE_ID, 'dashboard', $this->getEvent());
    }

    public function titleDashboard(): PageTitle
    {
        return new PageTitle(null, _('Payment dashboard'), 'fas fa-dashboard');
    }

    /**
     * @throws EventNotFoundException
     */
    public function renderDashboard(): void
    {
        $data = [];
        $paidCount = 0;
        $waitingCount = 0;
        $inProgressCount = 0;
        $noPaymentCount = 0;
        /** @var ScheduleGroupModel $group */
        foreach ($this->getEvent()->getScheduleGroups() as $group) {
            /** @var ScheduleItemModel $item */
            foreach ($group->getItems() as $item) {
                if ($item->payable) {
                    /** @var PersonScheduleModel $personSchedule */
                    foreach ($item->getInterested() as $personSchedule) {
                        if (!$personSchedule->isPaid()) {
                            $data[] = $personSchedule;
                        }
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
        }
        $this->template->paidCount = $paidCount;
        $this->template->waitingCount = $waitingCount;
        $this->template->noPaymentCount = $noPaymentCount;
        $this->template->inProgressCount = $inProgressCount;
        $this->template->rests = $data;
    }

    /**
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     */
    final public function renderDetail(): void
    {
        $payment = $this->getEntity();
        $this->template->model = $payment;
    }

    /**
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            null,
            Html::el('')
                ->addText(\sprintf(_('Detail of the payment %s'), $this->getEntity()->payment_id))
                ->addHtml(
                    Html::el('small')->addAttributes(['class' => 'ms-2'])->addHtml(
                        $this->getEntity()->state->badge()
                    )
                ),
            'fas fa-credit-card',
        );
    }


    /**
     * @throws EventNotFoundException
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    public function authorizedEdit(): bool
    {
        $event = $this->getEvent();
        return $this->eventAuthorizator->isAllowed($this->getEntity(), 'organizer', $event)
            || ($this->isPaymentAllowed() && $this->eventAuthorizator->isAllowed($this->getEntity(), 'edit', $event));
    }

    /**
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     */
    final public function renderEdit(): void
    {
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            \sprintf(_('Edit payment #%s'), $this->getEntity()->payment_id),
            'fas fa-credit-card'
        );
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of payments'), 'fas fa-credit-card');
    }

    /**
     * @throws EventNotFoundException
     */
    private function isPaymentAllowed(): bool
    {
        return time() < $this->getEvent()->begin->getTimestamp();
    }

    /**
     * @throws EventNotFoundException
     */
    protected function isEnabled(): bool
    {
        try {
            $this->getMachine();
        } catch (\Throwable $exception) {
            return false;
        }
        return $this->getEvent()->event_id === 180;
    }

    private function getMachine(): PaymentMachine
    {
        static $machine;
        if (!isset($machine)) {
            $machine = $this->eventDispatchFactory->getPaymentMachine();
        }
        return $machine;
    }

    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }

    protected function getORMService(): PaymentService
    {
        return $this->paymentService;
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     * @phpstan-return TransitionButtonsComponent<PaymentModel>
     */
    protected function createComponentButtonTransition(): TransitionButtonsComponent
    {
        return new TransitionButtonsComponent(
            $this->getContext(),
            $this->getMachine(), // @phpstan-ignore-line
            $this->getEntity()
        );
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): PaymentList
    {
        return new PaymentList($this->getContext(), $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): PaymentForm
    {
        return new PaymentForm(
            $this->getContext(),
            [$this->getEvent()],
            $this->getLoggedPerson(),
            $this->isAllowed(PaymentModel::RESOURCE_ID, 'organizer'),
            $this->getMachine(),
            null
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentEditForm(): PaymentForm
    {
        return new PaymentForm(
            $this->getContext(),
            [$this->getEvent()],
            $this->getLoggedPerson(),
            $this->isAllowed($this->getEntity(), 'organizer'),
            $this->getMachine(),
            $this->getEntity()
        );
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentPaymentQRCode(): PaymentQRCode
    {
        return new PaymentQRCode($this->getContext(), $this->getEntity());
    }
}
