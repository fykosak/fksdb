<?php

namespace FKSDB\Components\DatabaseReflection\Links;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Component;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;

/**
 * Class AbstractLink
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractLink {

    protected Component $component;

    public function setComponent(Component $component): void {
        $this->component = $component;
    }

    /**
     * @param AbstractModelSingle $model
     * @return Html
     * @throws BadRequestException
     * @throws InvalidLinkException
     */
    final public function __invoke(AbstractModelSingle $model): Html {
        return Html::el('a')->addAttributes([
            'class' => 'btn btn-outline-primary btn-sm',
            'href' => $this->createLink($model),
        ])->addText($this->getText());
    }

    abstract public function getText(): string;

    abstract public function getDestination(AbstractModelSingle $model): string;

    abstract public function prepareParams(AbstractModelSingle $model): array;

    abstract public function getModelClassName(): string;

    /**
     * @param AbstractModelSingle $model
     * @return string
     * @throws BadTypeException
     * @throws InvalidLinkException
     */
    private function createLink(AbstractModelSingle $model): string {

        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new BadTypeException($modelClassName, $model);
        }
        return $this->component->getPresenter()->link(
            $this->getDestination($model),
            $this->prepareParams($model)
        );
    }
}
