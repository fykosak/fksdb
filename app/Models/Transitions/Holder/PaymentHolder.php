<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Holder;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\ServicePayment;

class PaymentHolder implements ModelHolder
{
    private ?PaymentModel $model;
    private ServicePayment $service;

    public function __construct(?PaymentModel $model, ServicePayment $servicePayment)
    {
        $this->model = $model;
        $this->service = $servicePayment;
    }

    public function updateState(EnumColumn $newState): void
    {
        $this->service->updateModel($this->model, ['state' => $newState->value]);
    }

    public function getState(): ?EnumColumn
    {
        return isset($this->model) ? $this->model->state : null;
    }

    public function getModel(): ?PaymentModel
    {
        return $this->model;
    }

    public function updateData(array $data): void
    {
        if (isset($this->model)) {
            $this->service->updateModel($this->model, $data);
        } else {
            $this->model = $this->service->createNewModel($data);
        }
    }
}
