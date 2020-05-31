<?php

namespace FKSDB\Components\DatabaseReflection\Links;

use FKSDB\ORM\AbstractModelSingle;

/**
 * Class Link
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Link extends AbstractLink {

    private string $destination;

    private array $params;

    private string $title;

    private string $modelClassName;

    public function setParams(string $destination, array $params, string $title, string $modelClassName): void {
        $this->destination = $destination;
        $this->params = $params;
        $this->title = $title;
        $this->modelClassName = $modelClassName;
    }

    public function getText(): string {
        return _($this->title);
    }

    public function getModelClassName(): string {
        return $this->modelClassName;
    }

    public function getDestination(AbstractModelSingle $model): string {
        return $this->destination;
    }

    public function prepareParams(AbstractModelSingle $model): array {
        $urlParams = [];
        foreach ($this->params as $key => $accessKey) {
            $urlParams[$key] = $model->{$accessKey};
        }
        return $urlParams;
    }
}
