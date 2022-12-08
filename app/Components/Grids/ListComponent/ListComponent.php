<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent;

use FKSDB\Models\ORM\ORMFactory;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IContainer;
use Nette\DI\Container;

abstract class ListComponent extends BaseComponent implements IContainer
{
    protected ORMFactory $reflectionFactory;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->monitor(Presenter::class, function () {
            $this->configure();
        });
    }

    abstract protected function configure(): void;

    final public function createReferencedRow(string $name): ORMRow
    {
        $row = new ORMRow($this->container, $name);
        $this->addComponent($row, str_replace('.', '__', $name));
        return $row;
    }

    final public function createColumnsRow(string $name): ColumnsRow
    {
        $row = new ColumnsRow($this->container);
        $this->addComponent($row, $name);
        return $row;
    }
}
