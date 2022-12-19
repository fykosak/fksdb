<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent;

use Fykosak\NetteORM\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;

abstract class ItemComponent extends BaseComponent
{
    public string $className = '';

    abstract protected function getTemplatePath(): string;

    public function render(Model $model, int $userPermission): void
    {
        $this->template->className = $this->className;
        $this->template->model = $model;
        $this->template->userPermission = $userPermission;
        $this->template->render($this->getTemplatePath());
    }
}
