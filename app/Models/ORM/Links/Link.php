<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Links;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;

/**
 * @phpstan-template TModel of Model
 */
final class Link
{
    /** @phpstan-var class-string<TModel> */
    protected string $modelClassName;
    private string $destination;
    /** @phpstan-var array<string,string> */
    private array $params;
    private Title $title;

    /**
     * @phpstan-param class-string<TModel> $modelClassName
     * @phpstan-param array<string,string> $params
     */
    public function __construct(string $destination, array $params, string $label, string $icon, string $modelClassName)
    {
        $this->modelClassName = $modelClassName;
        $this->destination = $destination;
        $this->params = $params;
        $this->title = new Title(null, _($label), $icon);
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
     * @phpstan-return TModel
     */
    protected function getModel(Model $modelSingle): ?Model
    {
        return $modelSingle->getReferencedModel($this->modelClassName);
    }

    /**
     * @throws CannotAccessModelException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     * @phpstan-return array{string,array<string,scalar>}
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

    public function title(): Title
    {
        return $this->title;
    }

    /**
     * @phpstan-param TModel $model
     * @phpstan-return array<string,scalar>
     */
    private function prepareParams(Model $model): array
    {
        $urlParams = [];
        foreach ($this->params as $key => $accessKey) {
            $urlParams[$key] = $model->{$accessKey};
        }
        return $urlParams;
    }
}
