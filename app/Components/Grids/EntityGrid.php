<?php

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;
use Nette\Application\IPresenter;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class EntityGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class EntityGrid extends BaseGrid {

    protected AbstractServiceSingle $service;

    private array $queryParams;

    private array $columns;

    public function __construct(Container $container, string $serviceClassName, array $columns = [], array $queryParams = []) {
        parent::__construct($container);
        $this->service = $container->getByType($serviceClassName);
        $this->queryParams = $queryParams;
        $this->columns = $columns;
    }

    protected function getData(): IDataSource {
        $source = $this->service->getTable()->where($this->queryParams);
        return $this->createDataSource($source);
    }

    protected function createDataSource(Selection $source): IDataSource {
        return new NDataSource($source);
    }

    /**
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function configure(IPresenter $presenter): void {
        parent::configure($presenter);
        $this->addColumns($this->columns);
    }
}
