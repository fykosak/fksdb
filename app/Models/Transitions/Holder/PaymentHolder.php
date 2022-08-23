<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Holder;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\PaymentService;

class PaymentHolder implements ModelHolder
{
    private ?PaymentModel $model;
    private PaymentService $service;

    public function __construct(?PaymentModel $model, PaymentService $paymentService)
    {
        $this->model = $model;
        $this->service = $paymentService;
    }

    public function updateState(EnumColumn $newState): void
    {
        $this->service->storeModel(['state' => $newState->value], $this->model);
    }

    public function getState(): ?EnumColumn
    {
        return isset($this->model) ? $this->model->state : null;
    }

    public function getModel(): ?PaymentModel
    {
        return $this->model;
    }
}
