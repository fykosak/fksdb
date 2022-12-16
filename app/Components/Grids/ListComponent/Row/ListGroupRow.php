<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Row;

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

    public function render(Model $model): void
    {
        $this->template->className = $this->className;
        $this->template->model = $model;
        $this->template->models = ($this->modelToIterator)($model);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'listGroup.latte');
    }
}
