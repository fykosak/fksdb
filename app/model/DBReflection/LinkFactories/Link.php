<?php

namespace FKSDB\DBReflection\LinkFactories;

use FKSDB\ORM\AbstractModelSingle;

/**
 * Class Link
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Link extends AbstractLink {
    /** @var string */
    private $destination;
    /** @var array */
    private $params;
    /** @var string */
    private $title;

    /**
     * Link constructor.
     * @param string $destination
     * @param array $params
     * @param string $title
     */
    public function __construct(string $destination, array $params, string $title) {
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
