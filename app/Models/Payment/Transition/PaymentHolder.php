<?php

namespace FKSDB\Models\Payment\Transition;

use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Services\ServicePayment;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;

class PaymentHolder implements ModelHolder {

    private ?ModelPayment $model;
    private ServicePayment $service;

    public function __construct(?ModelPayment $model, ServicePayment $servicePayment) {
        $this->model = $model;
        $this->service = $servicePayment;
    }

    public static function createNew(array $data, ServicePayment $servicePayment): self {
        $model = $servicePayment->createNewModel($data);
        return new static($model, $servicePayment);
    }

    public function updateState(string $newState): void {
        $this->service->updateModel($this->model, ['state' => $newState]);
    }

    public function getState(): string {
        return isset($this->model) ? $this->model->state : Machine::STATE_INIT;
    }

    public function getModel(): ?AbstractModel {
        return $this->model;
    }

    public function updateData(array $data): void {
        if (isset($this->model)) {
            $this->service->updateModel($this->model, $data);
        } else {
            $this->model = $this->service->createNewModel($data);
        }
    }
}
