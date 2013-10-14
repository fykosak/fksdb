<?php

namespace FKSDB\Components\View;

use FKSDB\Components\Grids\StoredQueryGrid;
use Nette\Application\UI\PresenterComponent;
use PDOException;
use SQL\StoredQuery;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class StoredQueryResult extends PresenterComponent {

    /**
     * @var StoredQuery
     */
    private $storedQuery;

    function __construct(StoredQuery $storedQuery) {
        $this->storedQuery = $storedQuery;
    }

    protected function createComponentGrid($name) {
        $grid = new StoredQueryGrid($this->storedQuery);
        return $grid;
    }

    public function render() {
        try {
            $this->storedQuery->getColumnNames(); // this may throw PDOException in the main query
            $this['grid']->render();
        } catch (PDOException $e) {            
            echo $e->getMessage();
        }
    }

}
