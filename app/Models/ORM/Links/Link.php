<?php

namespace FKSDB\Models\ORM\Links;

use Fykosak\NetteORM\AbstractModel;

class Link extends LinkFactory {

    private string $destination;
    private array $params;
    private string $title;

    public function __construct(string $destination, array $params, string $title, string $modelClassName) {
        parent::__construct($modelClassName);
        $this->destination = $destination;
        $this->params = $params;
        $this->title = $title;
    }

    public function getText(): string {
        return _($this->title);
    }

    protected function getDestination(AbstractModel $model): string {
        return $this->destination;
    }

    protected function prepareParams(AbstractModel $model): array {
        $urlParams = [];
        foreach ($this->params as $key => $accessKey) {
            $urlParams[$key] = $model->{$accessKey};
        }
        return $urlParams;
    }
}
