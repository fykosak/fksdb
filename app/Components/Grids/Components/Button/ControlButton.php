<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Button;

use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Control;
use Nette\DI\Container;

class ControlButton extends DefaultButton
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

    public function render(Model $model, int $userPermission): void
    {
        $this->template->linkControl = $this->control;
        parent::render($model, $userPermission);
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR;
    }
}
