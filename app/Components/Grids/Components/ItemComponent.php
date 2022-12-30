<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use Fykosak\NetteORM\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

abstract class ItemComponent extends BaseComponent
{
    public Title $title;

    public function __construct(Container $container, Title $title)
    {
        parent::__construct($container);
        $this->title = $title;
    }

    abstract protected function getTemplatePath(): string;

    public function render(Model $model, int $userPermission): void
    {
        $this->template->model = $model;
        $this->template->title = $this->title;
        $this->template->userPermission = $userPermission;
        $this->template->render($this->getTemplatePath());
    }
}
