<?php

namespace FKSDB\Components\Grids;

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
        $c = 0;
        foreach ($this->storedQuery->getColumnNames() as $name) {
            $this->addColumn($c+1, $name)->setRenderer(function($row) use($c) {
                        echo $row[$c];
                    });
            ++$c;
        }

        //
        // operations
    //

    }

}
