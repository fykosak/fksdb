<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Links;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;

abstract class LinkFactory
{
    protected string $modelClassName;

    public function __construct(?string $modelClassName = null)
    {
        if ($modelClassName) {
            $this->modelClassName = $modelClassName;
        }
    }

    /**
     * @throws InvalidLinkException
     * @throws CannotAccessModelException
     * @throws \ReflectionException
     */
    public function create(Presenter $presenter, Model $model): string
    {
        return $presenter->link(...$this->createLinkParameters($model));
    }

    /**
     * @throws CannotAccessModelException
     * @throws \ReflectionException
     */
    protected function getModel(Model $modelSingle): ?Model
    {
        if (!isset($this->modelClassName)) {
            return $modelSingle;
        }
        return $modelSingle->getReferencedModel($this->modelClassName);
    }

    /**
     * @throws CannotAccessModelException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     */
    public function createLinkParameters(Model $model): array
    {
        $model = $this->getModel($model);
        if (is_null($model)) {
            throw new InvalidLinkException();
        }
        return [
            $this->getDestination($model),
            $this->prepareParams($model),
        ];
    }

    abstract protected function getDestination(Model $model): string;

    abstract protected function prepareParams(Model $model): array;
}
