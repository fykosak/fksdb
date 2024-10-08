<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\Transitions\Holder\PaymentHolder;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends Machine<PaymentHolder>
 */
final class PaymentMachine extends Machine
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @param PaymentModel $model
     */
    public function createHolder(Model $model): PaymentHolder
    {
        return new PaymentHolder($model, $this->paymentService);
    }
}
