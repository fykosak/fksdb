<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\ServicesMulti\Events;

use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Events\ModelDsefParticipant;
use FKSDB\Models\ORM\Tables\MultiTableSelection;
use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\Services\Events\ServiceDsefParticipant;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\ORM\ModelsMulti\Events\ModelMDsefParticipant;
use Fykosak\NetteORM\Model;
use Nette\SmartObject;

/**
 * @deprecated
 */
class ServiceMDsefParticipant
{
    use SmartObject;

    public EventParticipantService $mainService;
    public ServiceDsefParticipant $joinedService;

    public function __construct(EventParticipantService $mainService, ServiceDsefParticipant $joinedService)
    {
        $this->mainService = $mainService;
        $this->joinedService = $joinedService;
    }

    /**
     * Delete post contact including the address.
     * @throws ModelException
     */
    public function disposeModel(ModelMDsefParticipant $model): void
    {
        $this->checkType($model);
        $this->joinedService->disposeModel($model->joinedModel);
        $this->mainService->disposeModel($model->mainModel);
    }


    public function composeModel(
        EventParticipantModel $mainModel,
        ModelDsefParticipant $joinedModel
    ): ModelMDsefParticipant {
        return new ModelMDsefParticipant($mainModel, $joinedModel);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkType(ModelMDsefParticipant $model): void
    {
        if (!$model instanceof ModelMDsefParticipant::class) {
            throw new \InvalidArgumentException(
                'Service for class ' . $this->getModelClassName() . ' cannot store ' . get_class($model)
            );
        }
    }

    /**
     * Use this method to store a model!
     * @deprecated
     */
    public function storeModel(array $data, ?ModelMDsefParticipant $model = null): ModelMDsefParticipant
    {
        $mainModel = $this->mainService->storeModel($data, $model ? $model->mainModel : null);

        $joinedModel = $this->joinedService->storeModel(
            array_merge($data, [
                'event_participant_id' => $mainModel->getPrimary(),
            ]),
            $model ? $model->joinedModel : null
        );
        return $this->composeModel($mainModel, $joinedModel);
    }

    /**
     * @param mixed $key ID of the joined models
     */
    public function findByPrimary($key): ?ModelMDsefParticipant
    {
        $joinedModel = $this->joinedService->findByPrimary($key);
        if (!$joinedModel) {
            return null;
        }
        /** @var Model $mainModel */
        $mainModel = $this->mainService
            ->getTable()
            ->where('event_participant_id', $joinedModel->event_participant_id)
            ->fetch(); //?? is this always unique??
        return $this->composeModel($mainModel, $joinedModel);
    }

    public function getTable(): MultiTableSelection
    {
        $joinedTable = $this->joinedService->getTable()->getName();
        $mainTable = $this->mainService->getTable()->getName();

        $selection = new MultiTableSelection(
            $this,
            $joinedTable,
            $this->joinedService->explorer,
            $this->joinedService->explorer->getConventions()
        );
        // $selection->joinWhere($mainTable, "$joinedTable.{$mainTable}_id = $mainTable.{$mainTable}_id");
        // $selection->select("$joinedTable.*");
        $selection->select("$mainTable.*");

        return $selection;
    }

    /**
     * @return class-string<ModelMDsefParticipant>|string|ModelMDsefParticipant
     */
    final public function getModelClassName(): string
    {
        return ModelMDsefParticipant::class;
    }
}
