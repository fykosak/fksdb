<?php

namespace FKSDB\Components\Grids;

use PDOException;
use SQL\StoredQuery;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class StoredQueryGrid extends BaseGrid {

    /**
     * @var StoredQuery
     */
    private $storedQuery;

    function __construct(StoredQuery $storedQuery) {
        $this->storedQuery = $storedQuery;
    }

    protected function configure($presenter) {
        parent::configure($presenter);

        //
        // data
        //
        $this->setDataSource($this->storedQuery);

        //
        // columns
        //
        try {
            $c = 0;
            foreach ($this->storedQuery->getColumnNames() as $name) {
                $this->addColumn($c + 1, $name)->setRenderer(function($row) use($c) {
                            echo $row[$c];
                        });
                ++$c;
            }
        } catch (PDOException $e) {
            // pass, exception should be handled inn parent components
        }

        //
        // operations
        //
        $this->paginate = false;
    }

}
