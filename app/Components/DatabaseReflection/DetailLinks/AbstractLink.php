<?php

namespace FKSDB\Components\DatabaseReflection\Links;

use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\PresenterComponent;
use Nette\Utils\Html;

/**
 * Class AbstractLink
 * @package FKSDB\Components\DatabaseReflection\Links
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
     * @param $model
     * @return string
     * @throws InvalidLinkException
     * @throws BadRequestException
     */
    public abstract function createLink($model): string;
}
