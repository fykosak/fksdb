<?php

declare(strict_types=1);

namespace FKSDB\Modules\ShopModule;

use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use FKSDB\Models\Transitions\TransitionsMachineFactory;
use Nette\Application\UI\Template;

abstract class BasePresenter extends \FKSDB\Modules\Core\BasePresenter
{
    protected const AvailableEventIds = [182]; //phpcs:ignore

    protected TransitionsMachineFactory $machineFactory;

    public function injectMachineFactory(TransitionsMachineFactory $machineFactory): void
    {
        $this->machineFactory = $machineFactory;
    }

    final protected function getMachine(): PaymentMachine
    {
        static $machine;
        if (!isset($machine)) {
            $machine = $this->machineFactory->getPaymentMachine();
        }
        return $machine;
    }

    public function getContest(): ContestModel
    {
        /** @var ContestModel $contest */
        $contest = $this->contestService->findByPrimary(ContestModel::ID_FYKOS);
        return $contest;
    }

    protected function createTemplate(): Template
    {
        $template = parent::createTemplate();
        $template->payment = $this->getInProgressPayment();
        return $template;
    }

    public function getInProgressPayment(): ?PaymentModel
    {
        static $payment;
        if (!isset($payment)) {
            $person = $this->getLoggedPerson();
            /** @var PaymentModel|null $payment */
            $payment = $person->getPayments()->where('state', PaymentState::IN_PROGRESS)->fetch();
        }
        return $payment;
    }
}
