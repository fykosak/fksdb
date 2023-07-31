<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Button;

use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Control;
use Nette\DI\Container;

/**
 * @template M of \Fykosak\NetteORM\Model
 * @phpstan-extends Button<M>
 */
class ControlButton extends Button
{
    private Control $control;

    public function __construct(
        Container $container,
        Control $control,
        Title $title,
        callable $linkCallback,
        ?string $buttonClassName = null,
        ?callable $showCallback = null
    ) {
        parent::__construct($container, $title, $linkCallback, $buttonClassName, $showCallback);
        $this->control = $control;
    }

    protected function getLinkControl(): Control
    {
        return $this->control;
    }
}
