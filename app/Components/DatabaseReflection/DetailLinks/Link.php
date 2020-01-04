<?php

namespace FKSDB\Components\DatabaseReflection\Links;

use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;

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
     * @param string $destination
     * @param array $params
     * @param string $title
     */
    public function setParams(string $destination, array $params, string $title) {
        $this->destination = $destination;
        $this->params = $params;
        $this->title = $title;
    }

    /**
     * @inheritDoc
     */
    protected function getText(): string {
        return _($this->title);
    }

    /**
     * @inheritDoc
     */
    protected function createLink($model): string {
        $urlParams = [];
        foreach ($this->params as $key => $accessKey) {
            $urlParams[$key] = $model->{$accessKey};
        }
        return $this->presenterComponent->getPresenter()->link($this->destination, $urlParams);
    }
}
