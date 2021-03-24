<?php

namespace FKSDB\Models\TeamApplications;

use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use Fykosak\NetteORM\AbstractModel;

class TeamApplicationHolder implements ModelHolder {

    private ?ModelFyziklaniTeam $model;
    private ServiceFyziklaniTeam $service;

    public function __construct(?ModelFyziklaniTeam $model, ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->model = $model;
        $this->service = $serviceFyziklaniTeam;
    }

    public function updateState(string $newState): void {
        $this->service->updateModel2($this->model, ['status' => $newState]);
        $newModel = $this->service->refresh($this->model);
        $this->model = $newModel;
    }

    public function getState(): string {
        return isset($this->model) ? $this->model->status : Machine::STATE_INIT;
    }

    public function getModel(): ?AbstractModel {
        return $this->model;
    }

    public function updateData(array $data): void {
        $this->model = $this->service->store($this->model, $data);
    }
}
