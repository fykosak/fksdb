<?php

namespace FKSDB\Models\ORM\Links;

use FKSDB\Models\ORM\Models\AbstractModelSingle;

/**
 * Class Link
 * @author Michal Červeňák <miso@fykos.cz>
 */
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

    protected function getDestination(AbstractModelSingle $model): string {
        return $this->destination;
    }

    protected function prepareParams(AbstractModelSingle $model): array {
        $urlParams = [];
        foreach ($this->params as $key => $accessKey) {
            $urlParams[$key] = $model->{$accessKey};
        }
        return $urlParams;
    }
}
