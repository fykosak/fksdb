<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Button;

use FKSDB\Components\Grids\Components\ItemComponent;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

abstract class DefaultButton extends ItemComponent
{
    /** @var callable */
    private $linkCallback;
    /** @var callable|null */
    private $showCallback;
    private ?string $buttonClassName;

    public function __construct(
        Container $container,
        Title $title,
        callable $linkCallback,
        ?string $buttonClassName = null,
        ?callable $showCallback = null
    ) {
        parent::__construct($container, $title);
        $this->linkCallback = $linkCallback;
        $this->buttonClassName = $buttonClassName;
        $this->showCallback = $showCallback;
    }

    public function render(?Model $model, ?int $userPermission): void
    {
        $this->template->show = isset($this->showCallback) ? ($this->showCallback)($model, $userPermission) : true;
        $this->template->params = ($this->linkCallback)($model);
        $this->template->buttonClassName = $this->buttonClassName ?? 'btn btn-outline-secondary btn-sm';
        parent::render($model, $userPermission);
    }
}
