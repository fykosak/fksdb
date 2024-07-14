<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Holder;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Services\PaymentService;

/**
 * @phpstan-implements ModelHolder<PaymentModel,PaymentState>
 */
final class PaymentHolder implements ModelHolder
{
    private PaymentModel $model;
    private PaymentService $service;

    public function __construct(PaymentModel $model, PaymentService $paymentService)
    {
        $this->model = $model;
        $this->service = $paymentService;
    }

    /**
     * @param PaymentState $newState
     */
    public function setState(EnumColumn $newState): void
    {
        $this->service->storeModel(['state' => $newState->value], $this->model);
    }

    public function getState(): PaymentState
    {
        return $this->model->state;
    }

    public function getModel(): PaymentModel
    {
        return $this->model;
    }
}
