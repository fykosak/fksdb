<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Row;

use FKSDB\Components\Controls\ColumnPrinter\ColumnPrinterComponent;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;

class ORMRow extends Row
{
    protected string $name;

    public function __construct(Container $container, string $name)
    {
        parent::__construct($container);
        $this->name = $name;
    }

    public function render(Model $model, FieldLevelPermissionValue $userPermission): void
    {
        $this->beforeRender($model, $userPermission);
        $this->template->name = $this->name;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'orm.latte');
    }

    protected function createComponentPrinter(): ColumnPrinterComponent
    {
        return new ColumnPrinterComponent($this->getContext());
    }
}
