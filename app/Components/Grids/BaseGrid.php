<?php

namespace FKSDB\Components\Grids;

use Exception;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\IPresenter;
use Nette\Application\UI\Form;
use Nette\Application\UI\InvalidLinkException;
use Nette\InvalidStateException;
use FKSDB\NotImplementedException;
use Nette\Templating\FileTemplate;
use Nette\Templating\ITemplate;
use Nette\Utils\Html;
use NiftyGrid\Components\Button;
use NiftyGrid\Components\Column;
use NiftyGrid\Components\GlobalButton;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;
use NiftyGrid\Grid;
use NiftyGrid\GridException;
use NiftyGrid\GridPaginator;
use PePa\CSVResponse;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class BaseGrid extends Grid {

    /** @persistent string */
    public $searchTerm;
    /**
     * @var TableReflectionFactory
     */
    protected $tableReflectionFactory;

    /**
     * BaseGrid constructor.
     * @param TableReflectionFactory|null $tableReflectionFactory
     */
    public function __construct(TableReflectionFactory $tableReflectionFactory = null) {
        parent::__construct();
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @param $presenter
     */
    protected function configure($presenter) {
        $this->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.latte');
        /**
         * @var GridPaginator $paginator
         */
        $paginator = $this->getComponent('paginator');
        $paginator->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.paginator.latte');
    }


    /**
     * @param null $class
     * @return ITemplate
     */
    protected function createTemplate($class = NULL): ITemplate {
        /**
         * @var GridPaginator $paginator
         * @var FileTemplate $template
         */
        $paginator = $this->getComponent('paginator');
        $paginator->getTemplate()->setTranslator($this->presenter->getTranslator());
        $template = parent::createTemplate($class);
        $template->setTranslator($this->presenter->getTranslator());
        return $template;
    }

    /*     * *****************************
     * Extended rendering for the paginator
     * ***************************** */
    /**
     * @throws GridException
     */
    public function render() {
        $paginator = $this->getPaginator();

        // this has to be done already here (and in the parent call again :-( )
        if ($this->searchTerm) {
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
        $this->getComponent('paginator')->getTemplate()->steps = $steps;

        parent::render();
    }

    /*     * ******************************
     * Search
     * ****************************** */

    /**
     * @return bool
     */
    public function isSearchable(): bool {
        return $this->dataSource instanceof SearchableDataSource;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentSearchForm(): FormControl {
        if (!$this->isSearchable()) {
            throw new InvalidStateException("Cannot create search form without searchable data source.");
        }
        $control = new FormControl();
        $form = $control->getForm();
        //$form = new Form();
        $form->setMethod(Form::GET);
        $form->addText('term')->setDefaultValue($this->searchTerm)->setAttribute('placeholder', _('Vyhledat'));
        $form->addSubmit('submit', _('Search'));
        $form->onSuccess[] = function (Form $form) {
            $values = $form->getValues();
            $this->searchTerm = $values['term'];
            $this->dataSource->applyFilter($values['term']);
            // TODO is this vv needed? vv
            $count = $this->dataSource->getCount();
            $this->getPaginator()->itemCount = $count;
        };
        return $control;
    }

    /*     * ***************************
     * Apperance
     * *************************** */

    /**
     * Adds button with Bootstrap CSS classes (default is 'default').
     * @param string $name
     * @param string $label
     * @return Button
     * @throws DuplicateButtonException
     */
    protected function addButton($name, $label = NULL): Button {
        $button = parent::addButton($name, $label);
        $button->setClass('btn btn-sm btn-secondary');
        return $button;
    }

    /**
     * @param $name
     * @param null $label
     * @return GlobalButton
     * @throws DuplicateGlobalButtonException
     */
    public function addGlobalButton($name, $label = NULL): GlobalButton {
        $button = parent::addGlobalButton($name, $label);
        $button->setClass('btn btn-sm btn-primary');
        return $button;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param string|AbstractModelSingle $modelClassName
     * @throws DuplicateColumnException
     * @throws Exception
     */
    protected function addReflectionColumn(string $tableName, string $fieldName, string $modelClassName) {
        $factory = $this->tableReflectionFactory->loadService($tableName, $fieldName);

        $this->addColumn($fieldName, $factory->getTitle())->setRenderer(function ($model) use ($factory, $fieldName, $modelClassName) {
            if (!$model instanceof $modelClassName) {
                $model = $modelClassName::createFromActiveRow($model);
            }
            return $factory->renderValue($model, 1);
        });
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param callable $accessCallback ActiveRow=>AbstractModelSingle
     * @throws DuplicateColumnException
     * @throws Exception
     */
    protected function addJoinedColumn(string $tableName, string $fieldName, callable $accessCallback) {
        $factory = $this->tableReflectionFactory->loadService($tableName, $fieldName);
        $this->addColumn($fieldName, $factory->getTitle())->setRenderer(function ($row) use ($factory, $fieldName, $accessCallback) {
            $model = $accessCallback($row);
            return $factory->renderValue($model, 1);
        });
    }

    /**
     * @return string|AbstractModelSingle
     * @throws NotImplementedException
     */
    protected function getModelClassName(): string {
        throw new NotImplementedException();
    }

    /**
     * @param array $fields
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     */
    protected function addColumns(array $fields) {
        foreach ($fields as $name) {
            list($table, $field) = TableReflectionFactory::parseRow($name);
            $this->addReflectionColumn($table, $field, $this->getModelClassName());
        }
    }

    /**
     * @param IPresenter $presenter
     * @param string $destination
     * @param string $id
     * @param string $label
     * @param bool $checkACL
     * @param array $params
     * @return Button
     * @throws DuplicateButtonException
     * @throws NotImplementedException
     */
    protected function addLinkButton(IPresenter $presenter, string $destination, string $id, string $label, bool $checkACL = true, array $params = []): Button {
        $modelClassName = $this->getModelClassName();
        $paramMapCallback = function ($model) use ($params): array {
            $URLParams = [];
            foreach ($params as $key => $value) {
                $URLParams[$key] = $model->{$value};
            }
            return $URLParams;
        };
        return $this->addButton($id, $label)
            ->setText($label)
            ->setShow(function ($row) use ($presenter, $modelClassName, $destination, $checkACL, $paramMapCallback) {
                if (!$checkACL) {
                    return true;
                }
                $model = $modelClassName::createFromActiveRow($row);
                return $presenter->authorized($destination, $paramMapCallback($model));
            })
            ->setLink(function ($row) use ($modelClassName, $destination, $paramMapCallback) {
                $model = $modelClassName::createFromActiveRow($row);
                return $this->getPresenter()->link($destination, $paramMapCallback($model));
            });
    }

    /**
     * @param $linkId
     * @param bool $checkACL
     * @return mixed
     * @throws DuplicateButtonException
     * @throws Exception
     */
    protected function addLink($linkId, bool $checkACL = false) {
        $modelClassName = $this->getModelClassName();
        $factory = $this->tableReflectionFactory->loadLinkFactory($linkId);
        $button = $this->addButton(str_replace('.', '_', $linkId), $factory->getText())
            ->setText($factory->getText())
            ->setLink(function ($row) use ($modelClassName, $factory) {
                $model = $modelClassName::createFromActiveRow($row);
                return $this->getPresenter()->link($factory->getDestination($model), $factory->prepareParams($model));
            });
        if ($checkACL) {
            $button->setShow(function ($row) use ($modelClassName, $checkACL, $factory) {
                if (!$checkACL) {
                    return true;
                }
                $model = $modelClassName::createFromActiveRow($row);
                return $this->getPresenter()->authorized($factory->getDestination($model), $factory->prepareParams($model));
            });
        }
        return $button;
    }

    /**
     * @return GlobalButton
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     */
    protected function addCSVDownloadButton(): GlobalButton {
        return $this->addGlobalButton('csv')
            ->setLabel(_('Download as csv'))
            ->setLink($this->link('csv!'));
    }

    /**
     * @throws AbortException
     */
    public function handleCsv() {
        $columns = $this['columns']->components;
        $rows = $this->dataSource->getData();
        $data = [];
        foreach ($rows as $row) {
            $datum = [];
            /**
             * @var Column $column
             */
            foreach ($columns as $column) {
                $item = $column->prepareValue($row);
                if ($item instanceof Html) {
                    $item = $item->getText();
                }
                $datum[$column->name] = $item;
            }
            $data[] = $datum;
        }
        $response = new CSVResponse($data, 'test.csv');
        $response->setAddHeading(true);
        $response->setQuotes(true);
        $response->setGlue(',');
        $this->getPresenter()->sendResponse($response);
    }

}
