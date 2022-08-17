<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\ServicesMulti;

use FKSDB\Models\ORM\ModelsMulti\ModelMulti;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Services\OldServiceSingle;
use FKSDB\Models\ORM\Tables\MultiTableSelection;
use Nette\SmartObject;

/**
 * Service for object representing one side of M:N relation, or entity in is-a relation ship.
 * Joined side is in a sense primary (search, select, delete).
 * @deprecated
 */
abstract class ServiceMulti
{
    use SmartObject;

    public OldServiceSingle $mainService;
    public OldServiceSingle $joinedService;
    public string $joiningColumn;
    private string $modelClassName;

    public function __construct(
        OldServiceSingle $mainService,
        OldServiceSingle $joinedService,
        string $joiningColumn,
        string $modelClassName
    ) {
        $this->mainService = $mainService;
        $this->joinedService = $joinedService;
        $this->modelClassName = $modelClassName;
        $this->joiningColumn = $joiningColumn;
    }

    public function composeModel(Model $mainModel, Model $joinedModel): ModelMulti
    {
        $className = $this->getModelClassName();
        return new $className($mainModel, $joinedModel);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkType(ModelMulti $model): void
    {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new \InvalidArgumentException(
                'Service for class ' . $this->getModelClassName() . ' cannot store ' . get_class($model)
            );
        }
    }

    /**
     * Use this method to store a model!
     * @deprecated
     */
    public function storeModel(array $data, ?ModelMulti $model = null): ModelMulti
    {
        $mainModel = $this->mainService->storeModel($data, $model ? $model->mainModel : null);

        $joinedModel = $this->joinedService->storeModel(
            array_merge($data, [
                $this->joiningColumn => $mainModel->getPrimary(),
            ]),
            $model ? $model->joinedModel : null
        );
        return $this->composeModel($mainModel, $joinedModel);
    }

    /**
     * Use this method to delete a model!
     * @throws \InvalidArgumentException
     */
    public function disposeModel(ModelMulti $model): void
    {
        $this->checkType($model);
        $this->joinedService->disposeModel($model->joinedModel);
        //TODO here should be deletion of mainModel as well, consider parametrizing this
    }

    /**
     * @param mixed $key ID of the joined models
     */
    public function findByPrimary($key): ?ModelMulti
    {
        $joinedModel = $this->joinedService->findByPrimary($key);
        if (!$joinedModel) {
            return null;
        }
        /** @var Model $mainModel */
        $mainModel = $this->mainService
            ->getTable()
            ->where($this->joiningColumn, $joinedModel->{$this->joiningColumn})
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
     * @return class-string<ModelMulti>|string|ModelMulti
     */
    final public function getModelClassName(): string
    {
        return $this->modelClassName;
    }
}
