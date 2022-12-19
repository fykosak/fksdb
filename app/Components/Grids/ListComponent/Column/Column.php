<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Column;

use FKSDB\Models\ORM\FieldLevelPermissionValue;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;
use FKSDB\Components\Grids\ListComponent\ItemComponent;

abstract class Column extends ItemComponent
{
    public string $className = 'col';

    protected function beforeRender(Model $model, FieldLevelPermissionValue $userPermission): void
    {
        $this->template->className = $this->className;
        $this->template->model = $model;
        $this->template->userPermission = $userPermission;
    }
}
