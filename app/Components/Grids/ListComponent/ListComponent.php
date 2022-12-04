<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent;

use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

abstract class ListComponent extends BaseComponent
{
    /** @var ListRow[] */
    protected array $rows = [];

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->monitor(Presenter::class, function () {
            $this->configure();
        });
    }

    public function createRow(): ListRow
    {
        $row = new ListRow();
        $this->rows[] = $row;
        return $row;
    }

    abstract protected function configure(): void;
}
