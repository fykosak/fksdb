<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\ServicesMulti;

use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Services\OldAbstractServiceSingle;
use FKSDB\Models\ORM\Tables\MultiTableSelection;
use Nette\SmartObject;

/**
 * Service for object representing one side of M:N relation, or entity in is-a relation ship.
 * Joined side is in a sense primary (search, select, delete).
 * @deprecated
 */
abstract class AbstractServiceMulti
{
    use SmartObject;

    public OldAbstractServiceSingle $mainService;
    public OldAbstractServiceSingle $joinedService;
    public string $joiningColumn;
    private string $modelClassName;

    public function __construct(
        OldAbstractServiceSingle $mainService,
        OldAbstractServiceSingle $joinedService,
        string $joiningColumn,
        string $modelClassName
    ) {
        $this->mainService = $mainService;
        $this->joinedService = $joinedService;
        $this->modelClassName = $modelClassName;
        $this->joiningColumn = $joiningColumn;
    }

    /**
     * Use this method to create new models!
     * @throws ModelException
     */
    public function createNewModel(array $data): AbstractModelMulti
    {
        $mainModel = $this->mainService->createNewModel($data);
        $data[$this->joiningColumn] = $mainModel->{$this->joiningColumn};
        $joinedModel = $this->joinedService->createNewModel($data);
        return $this->composeModel($mainModel, $joinedModel);
    }

    public function composeModel(AbstractModel $mainModel, AbstractModel $joinedModel): AbstractModelMulti
    {
        $className = $this->getModelClassName();
        return new $className($mainModel, $joinedModel);
    }

    /**
     * @throws ModelException
     */
    public function updateModel(AbstractModelMulti $model, array $data): bool
    {
        $this->checkType($model);
        $this->mainService->updateModel($model->mainModel, $data);
        return $this->joinedService->updateModel($model->joinedModel, $data);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkType(AbstractModelMulti $model): void
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
    public function storeModel(array $data, ?AbstractModelMulti $model = null): AbstractModelMulti
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
    public function dispose(AbstractModelMulti $model): void
    {
        $this->checkType($model);
        $this->joinedService->dispose($model->joinedModel);
        //TODO here should be deletion of mainModel as well, consider parametrizing this
    }

    /**
     * @param mixed $key ID of the joined models
     */
    public function findByPrimary($key): ?AbstractModelMulti
    {
        $joinedModel = $this->joinedService->findByPrimary($key);
        if (!$joinedModel) {
            return null;
        }
        /** @var AbstractModel $mainModel */
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
     * @return class-string<AbstractModelMulti>|string|AbstractModelMulti
     */
    final public function getModelClassName(): string
    {
        return $this->modelClassName;
    }
}
