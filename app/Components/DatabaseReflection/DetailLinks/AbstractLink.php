<?php

namespace FKSDB\Components\DatabaseReflection\Links;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\PresenterComponent;
use Nette\Utils\Html;

/**
 * Class AbstractLink
 * *
 */
abstract class AbstractLink {
    /**
     * @var PresenterComponent
     */
    protected $presenterComponent;

    /**
     * AbstractLink constructor.
     * @param PresenterComponent $presenterComponent
     */
    public function __construct(PresenterComponent $presenterComponent) {
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

    abstract public function getModelClassName(): string;

    /**
     * @param AbstractModelSingle $model
     * @return string
     * @throws InvalidLinkException
     * @throws BadRequestException
     */
    public function createLink($model): string {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new BadTypeException($modelClassName, $model);
        }
        return $this->presenterComponent->getPresenter()->link(
            $this->getDestination($model),
            $this->prepareParams($model)
        );
    }
}
