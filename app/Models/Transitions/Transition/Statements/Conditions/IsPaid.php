<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\Transitions\Holder\PersonScheduleHolder;
use FKSDB\Models\Transitions\Statement;

/**
 * @phpstan-implements Statement<bool,PersonScheduleHolder>
 */
class IsPaid implements Statement
{
    /**
     * @throws \Exception
     */
    public function __invoke(...$args): bool
    {
        /** @var PersonScheduleHolder $holder */
        [$holder] = $args;
        $model = $holder->getModel();
        if ($model->schedule_item->payable) {
            return true; // ak sa nedá zaplatiť je zaplatená
        }
        $payment = $model->getPayment();
        if (!$payment) {
            return false;
        }
        return $payment->state->value === PaymentState::RECEIVED;
    }
}
