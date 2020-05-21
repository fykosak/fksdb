<?php

namespace FKSDB\Components\DatabaseReflection\Links;

use FKSDB\ORM\AbstractModelSingle;

/**
 * Class Link
 * @package FKSDB\Components\DatabaseReflection\Links
 */
class Link extends AbstractLink {
    /**
     * @var string
     */
    private $destination;
    /**
     * @var array
     */
    private $params;
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $modelClassName;

    /**
     * @param string $destination
     * @param array $params
     * @param string $title
     * @param string $modelClassName
     */
    public function setParams(string $destination, array $params, string $title, string $modelClassName) {
        $this->destination = $destination;
        $this->params = $params;
        $this->title = $title;
        $this->modelClassName = $modelClassName;
    }

    /**
     * @inheritDoc
     */
    public function getText(): string {
        return _($this->title);
    }

    /**
     * @inheritDoc
     */
    public function getModelClassName(): string {
        return $this->modelClassName;
    }

    /**
     * @inheritDoc
     */
    public function getDestination($model): string {
        return $this->destination;
    }

    /**
     * @param AbstractModelSingle $model
     * @return array
     */
    public function prepareParams($model): array {
        $urlParams = [];
        foreach ($this->params as $key => $accessKey) {
            $urlParams[$key] = $model->{$accessKey};
        }
        return $urlParams;
    }
}
