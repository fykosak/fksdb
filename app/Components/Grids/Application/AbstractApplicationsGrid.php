<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\SQL\SearchableDataSource;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateColumnException;

abstract class AbstractApplicationsGrid extends BaseGrid
{
    protected EventModel $event;
    private Holder $holder;

    public function __construct(EventModel $event, Holder $holder, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
        $this->holder = $holder;
    }

    protected function getData(): IDataSource
    {
        $participants = $this->getSource();
        $source = new SearchableDataSource($participants);
        $source->setFilterCallback($this->getFilterCallBack());
        return $source;
    }

    abstract protected function getSource(): Selection;


    /**
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->addHolderColumns();
    }

    public function getFilterCallBack(): callable
    {
        return function (Selection $table, array $value): void {
            $states = [];
            foreach ($value['status'] as $state => $value) {
                if ($value) {
                    $states[] = str_replace('__', '.', $state);
                }
            }
            if (count($states)) {
                $table->where('status IN ?', $states);
            }
        };
    }

    abstract protected function getHoldersColumns(): array;

    /**
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function addHolderColumns(): void
    {
        $holderFields = $this->holder->primaryHolder->getFields();
        $fields = [];
        foreach ($holderFields as $name => $def) {
            if (in_array($name, $this->getHoldersColumns())) {
                $fields[] = $this->getTableName() . '.' . $name;
            }
        }
        $this->addColumns($fields);
    }

    abstract protected function getTableName(): string;
}
