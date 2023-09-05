<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Button;

use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Control;
use Nette\DI\Container;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model
 * @phpstan-extends Button<TModel>
 */
class PresenterButton extends Button
{
    /**
     * @phpstan-param callable(TModel):array{string,array<string,scalar>} $linkCallback
     * @phpstan-param (callable(TModel,int):bool)|null $showCallback
     */
    public function __construct(
        Container $container,
        ?Title $title,
        Title $buttonLabel,
        callable $linkCallback,
        ?string $buttonClassName = null,
        ?callable $showCallback = null
    ) {
        parent::__construct($container, $title, $buttonLabel, $linkCallback, $buttonClassName, $showCallback);
    }

    protected function getLinkControl(): Control
    {
        return $this->getPresenter();
    }
}
