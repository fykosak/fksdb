<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Links;

use Fykosak\NetteORM\Model;

/**
 * @template M of Model
 * @phpstan-extends LinkFactory<M>
 */
class Link extends LinkFactory
{
    private string $destination;
    private array $params;
    private string $title;

    /**
     * @param class-string<M> $modelClassName
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
     * @phpstan-param M $model
     */
    protected function getDestination(Model $model): string
    {
        return $this->destination;
    }

    /**
     * @phpstan-param M $model
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
