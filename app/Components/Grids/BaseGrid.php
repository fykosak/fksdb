<?php

namespace FKSDB\Components\Grids;

use Nette\Application\UI\Form;
use Nette\InvalidStateException;
use NiftyGrid\Grid;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class BaseGrid extends Grid {
    
    /** @persistent string */
    public $searchTerm;
    
    protected function configure($presenter) {
        $this->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.latte');
        $this['paginator']->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.paginator.latte');
    }

    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        $template->setTranslator($this->presenter->getTranslator());
        return $template;
    }

    /*     * *****************************
     * Extended rendering for the paginator
     * ***************************** */

    public function render() {
        $paginator = $this->getPaginator();
        
        // this has to be done already here (and in the parent call again :-( )
        if($this->searchTerm) {
            $this->dataSource->applyFilter($this->searchTerm);
        }
        $count = $this->getCount();
        $this->getPaginator()->itemCount = $count;
        /*
         * Credits David Grudl.
         * @see http://addons.nette.org/cs/visualpaginator
         */
        $page = $paginator->page;
        if ($paginator->pageCount < 2) {
            $steps = array($page);
        } else {
            $arr = range(max($paginator->firstPage, $page - 3), min($paginator->lastPage, $page + 3));
            $count = 4;
            $quotient = ($paginator->pageCount - 1) / $count;
            for ($i = 0; $i <= $count; $i++) {
                $arr[] = round($quotient * $i) + $paginator->firstPage;
            }
            sort($arr);
            $steps = array_values(array_unique($arr));
        }
        $this['paginator']->getTemplate()->steps = $steps;

        parent::render();
    }

    /*     * ******************************
     * Search 
     * ****************************** */

    public function isSearchable() {
        return $this->dataSource instanceof SearchableDataSource;
    }

    protected function createComponentSearchForm($name) {
        if (!$this->isSearchable()) {
            throw new InvalidStateException("Cannot create search form without searchable data source.");
        }

        $form = new Form();
        $form->setMethod(Form::GET);
        $form->addText('term')->setDefaultValue($this->searchTerm);
        
        $that = $this;
        $form->onSuccess[] = function(Form $form) use($that) {
                    $values = $form->getValues();
                    $that->searchTerm=$values['term'];
                    $that->dataSource->applyFilter($values['term']);
                    // TODO is this vv needed? vv
                    $count = $that->dataSource->getCount();
                    $this->getPaginator()->itemCount = $count;
                };
        return $form;
    }

    /*     * ***************************
     * Apperance
     * *************************** */

    /**
     * Adds button with Bootstrap CSS classes (default is 'default').
     * 
     * @param string $name
     * @param string $label
     */
    protected function addButton($name, $label = NULL) {
        $button = parent::addButton($name, $label);
        $button->setClass('btn btn-sm btn-primary');
        return $button;
    }

    public function addGlobalButton($name, $label = NULL) {
        $button = parent::addGlobalButton($name, $label);
        $button->setClass('btn btn-primary');
        return $button;
    }

}
