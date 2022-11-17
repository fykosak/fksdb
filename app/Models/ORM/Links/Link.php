<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Links;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;

final class Link
{
    public function __construct(
        private readonly string $destination,
        private readonly array $params,
        private readonly string $title,
        protected readonly string $modelClassName
    ) {
    }

    public function getText(): string
    {
        return _($this->title);
    }

    private function prepareParams(Model $model): array
    {
        $urlParams = [];
        foreach ($this->params as $key => $accessKey) {
            $urlParams[$key] = $model->{$accessKey};
        }
        return $urlParams;
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
    private function getModel(Model $modelSingle): ?Model
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
            $this->destination,
            $this->prepareParams($model),
        ];
    }
}
