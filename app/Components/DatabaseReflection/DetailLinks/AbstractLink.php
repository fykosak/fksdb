<?php

namespace FKSDB\Components\DatabaseReflection\Links;

use FKSDB\Exceptions\BadTypeException;
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
    final public function __invoke($model): Html {
        return Html::el('a')->addAttributes([
            'class' => 'btn btn-outline-primary btn-sm',
            'href' => $this->createLink($model),
        ])->addText($this->getText());
    }

    /**
     * @return string
     */
    abstract public function getText(): string;

    /**
     * @param AbstractModelSingle $model
     * @return string
     */
    abstract public function getDestination($model): string;

    /**
     * @param AbstractModelSingle $model
     * @return array
     */
    abstract public function prepareParams($model): array;

    /**
     * @return string
     */
    abstract public function getModelClassName(): string;

    /**
     * @param $model
     * @return string
     * @throws BadRequestException
     * @throws InvalidLinkException
     */
    public function createLink($model): string {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new BadTypeException($modelClassName, $model);
        }
        return $this->presenterComponent->link(
            $this->getDestination($model),
            $this->prepareParams($model)
        );
    }
}
