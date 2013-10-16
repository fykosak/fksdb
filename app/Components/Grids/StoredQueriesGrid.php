<?php

namespace FKSDB\Components\Grids;

use Authorization\ContestAuthorizator;
use NiftyGrid\DataSource\NDataSource;
use ServiceStoredQuery;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class StoredQueriesGrid extends BaseGrid {

    /**
     * @var ServiceStoredQuery
     */
    private $serviceStoredQuery;

    /**
     * @var ContestAuthorizator
     */
    private $contestAuthorizator;

    function __construct(ServiceStoredQuery $serviceStoredQuery, ContestAuthorizator $contestAuthorizator) {
        $this->serviceStoredQuery = $serviceStoredQuery;
        $this->contestAuthorizator = $contestAuthorizator;
    }

    protected function configure($presenter) {
        parent::configure($presenter);

        //
        // data
        //
        $queries = $this->serviceStoredQuery->getTable();

        $this->setDataSource(new NDataSource($queries));

        //
        // columns
        //
        $this->addColumn('name', 'Název');
        $this->addColumn('description', 'Popis');

        //
        // operations
        //
        $that = $this;
        $contest = $presenter->getSelectedContest();
        $this->addButton("edit", "Upravit")
                ->setClass("edit")
                ->setText('Upravit') //todo i18n
                ->setLink(function($row) use ($that) {
                            return $that->getPresenter()->link("edit", $row->query_id);
                        })
                ->setShow(function($row) use ($that, $contest) {
                            return $that->contestAuthorizator->isAllowed($row, 'edit', $contest);
                        });
        $this->addButton("show", "Zobrazit")
                ->setClass("show")
                ->setText('Zobrazit') //todo i18n
                ->setLink(function($row) use ($that) {
                            return $that->getPresenter()->link("show", $row->query_id);
                        })
                ->setShow(function($row) use ($that, $contest) {
                            return $that->contestAuthorizator->isAllowed($row, 'show', $contest);
                        });

        $this->addButton("execute", "Spustit")
                ->setClass("execute")
                ->setText('Spustit') //todo i18n
                ->setLink(function($row) use ($that) {
                            return $that->getPresenter()->link("execute", $row->query_id);
                        })
                ->setShow(function($row) use ($that, $contest) {
                            return $that->contestAuthorizator->isAllowed($row, 'execute', $contest);
                        });
    }

}
