<?php

namespace FKSDB\Components\DatabaseReflection\Links;

use FKSDB\ORM\AbstractModelSingle;

/**
 * Class Link
 * @author Michal Červeňák <miso@fykos.cz>
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
     * @return void
     */
    public function setParams(string $destination, array $params, string $title, string $modelClassName) {
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
