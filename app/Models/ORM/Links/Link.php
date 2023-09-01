<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Links;

use Fykosak\NetteORM\Model;

/**
 * @phpstan-template TModel of Model
 * @phpstan-extends LinkFactory<TModel>
 */
class Link extends LinkFactory
{
    private string $destination;
    /** @phpstan-var array<string,string> */
    private array $params;
    private string $title;

    /**
     * @phpstan-param class-string<TModel> $modelClassName
     * @phpstan-param array<string,string> $params
     */
    public function __construct(string $destination, array $params, string $title, string $modelClassName)
    {
        parent::__construct($modelClassName);
        $this->destination = $destination;
        $this->params = $params;
        $this->title = $title;
    }

    public function getText(): string
    {
        return _($this->title);
    }

    /**
     * @phpstan-param TModel $model
     */
    protected function getDestination(Model $model): string
    {
        return $this->destination;
    }

    /**
     * @phpstan-param TModel $model
     * @phpstan-return array<string,scalar>
     */
    protected function prepareParams(Model $model): array
    {
        $urlParams = [];
        foreach ($this->params as $key => $accessKey) {
            $urlParams[$key] = $model->{$accessKey};
        }
        return $urlParams;
    }
}
