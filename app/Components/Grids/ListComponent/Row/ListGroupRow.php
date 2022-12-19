<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Row;

use FKSDB\Models\ORM\FieldLevelPermissionValue;
use Fykosak\NetteORM\Model;

class ListGroupRow extends ColumnsRow
{
    /** @var callable */
    private $modelToIterator;
    public string $className = 'list-group list-group-flush';

    public function setModelToIterator(callable $callback): void
    {
        $this->modelToIterator = $callback;
    }

    public function render(Model $model, FieldLevelPermissionValue $userPermission): void
    {
        $this->template->models = ($this->modelToIterator)($model);
        parent::render($model, $userPermission);
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'listGroup.latte';
    }
}
