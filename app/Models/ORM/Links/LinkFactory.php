<?php

namespace FKSDB\Models\ORM\Links;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\ReferencedAccessor;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;

abstract class LinkFactory {

    protected string $modelClassName;

    public function __construct(?string $modelClassName = null) {
        if ($modelClassName) {
            $this->modelClassName = $modelClassName;
        }
    }

    /**
     * @throws InvalidLinkException
     * @throws CannotAccessModelException
     */
    public function create(Presenter $presenter, AbstractModel $model): string {
        return $presenter->link(...$this->createLinkParameters($model));
    }

    /**
     * @throws CannotAccessModelException
     */
    protected function getModel(AbstractModel $modelSingle): ?AbstractModel {
        if (!isset($this->modelClassName)) {
            return $modelSingle;
        }
        return ReferencedAccessor::accessModel($modelSingle, $this->modelClassName);
    }

    /**
     * @throws CannotAccessModelException
     * @throws InvalidLinkException
     */
    public function createLinkParameters(AbstractModel $model): array {
        $model = $this->getModel($model);
        if (is_null($model)) {
            throw new InvalidLinkException();
        }
        return [
            $this->getDestination($model),
            $this->prepareParams($model),
        ];
    }

    abstract protected function getDestination(AbstractModel $model): string;

    abstract protected function prepareParams(AbstractModel $model): array;
}
