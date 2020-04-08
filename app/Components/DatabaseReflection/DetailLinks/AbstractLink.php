<?php

namespace FKSDB\Components\DatabaseReflection\Links;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\BadRequestException;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;

/**
 * Class AbstractLink
 * @package FKSDB\Components\DatabaseReflection\Links
 */
abstract class AbstractLink {
    /**
     * @var LinkGenerator
     */
    protected $presenterComponent;

    /**
     * AbstractLink constructor.
     * @param LinkGenerator $presenterComponent
     */
    public function __construct(LinkGenerator $presenterComponent) {
        $this->presenterComponent = $presenterComponent;
    }

    /**
     * @param $model
     * @return Html
     * @throws BadRequestException
     * @throws InvalidLinkException
     */
    public final function __invoke($model): Html {
        return Html::el('a')->addAttributes([
            'class' => 'btn btn-outline-primary btn-sm',
            'href' => $this->createLink($model),
        ])->addText($this->getText());
    }

    /**
     * @return string
     */
    public abstract function getText(): string;

    /**
     * @param AbstractModelSingle $model
     * @return string
     */
    public abstract function getDestination($model): string;

    /**
     * @param AbstractModelSingle $model
     * @return array
     */
    public abstract function prepareParams($model): array;

    /**
     * @return string
     */
    public abstract function getModelClassName(): string;

    /**
     * @param $model
     * @return string
     * @throws BadRequestException
     * @throws InvalidLinkException
     */
    public function createLink($model): string {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new BadRequestException();
        }
        return $this->presenterComponent->link(
            $this->getDestination($model),
            $this->prepareParams($model)
        );
    }
}
