<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Button;

use FKSDB\Components\Grids\ListComponent\ItemComponent;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;

class DefaultButton extends ItemComponent
{
    /** @var callable */
    private $linkCallback;
    private string $title;
    private ?string $buttonClassName;

    public function __construct(
        Container $container,
        string $title,
        callable $linkCallback,
        ?string $buttonClassName = null
    ) {
        parent::__construct($container);
        $this->title = $title;
        $this->linkCallback = $linkCallback;
        $this->buttonClassName = $buttonClassName;
    }

    public function render(Model $model, int $userPermission): void
    {
        $this->template->params = ($this->linkCallback)($model);
        $this->template->title = $this->title;
        $this->template->buttonClassName = $this->buttonClassName ?? 'btn btn-outline-secondary btn-sm';
        parent::render($model, $userPermission);
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'default.latte';
    }
}
