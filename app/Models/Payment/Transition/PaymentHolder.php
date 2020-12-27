<?php

namespace FKSDB\Models\Payment\Transition;

use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Services\ServicePayment;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class PaymentHolder implements ModelHolder {

    private ?ModelPayment $model;
    private ServicePayment $service;

    public function __construct(ModelPayment $model, ServicePayment $servicePayment) {
        $this->model = $model;
        $this->service = $servicePayment;
    }

    public function updateState(string $newState): self {
        $this->service->updateModel2($this->model, ['state' => $newState]);
        $newModel = $this->service->refresh($this->model);
        return new static($newModel, $this->service);
    }

    public function getState(): string {
        return $this->model->state;
    }

    public function getModel(): ?AbstractModelSingle {
        return $this->model;
    }
}
