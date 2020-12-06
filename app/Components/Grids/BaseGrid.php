<?php

namespace FKSDB\Components\Grids;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\DBReflection\DBReflectionFactory;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\ORM\Models\AbstractModelSingle;
use FKSDB\SQL\SearchableDataSource;
use Nette\Application\AbortException;
use Nette\Application\IPresenter;
use Nette\Application\UI\Form;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\ITemplate;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\Localization\ITranslator;
use Nette\Utils\Html;
use NiftyGrid\Components\Button;
use NiftyGrid\Components\Column;
use NiftyGrid\Components\GlobalButton;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;
use NiftyGrid\Grid;
use NiftyGrid\GridException;
use NiftyGrid\GridPaginator;
use PePa\CSVResponse;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class BaseGrid extends Grid {
    /** @persistent string */
    public $searchTerm;

    protected DBReflectionFactory $tableReflectionFactory;

    private Container $container;

    public function __construct(Container $container) {
        parent::__construct();
        $this->container = $container;
        $container->callInjects($this);
    }

    final public function injectBase(DBReflectionFactory $tableReflectionFactory, ITranslator $translator): void {
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->setTranslator($translator);
    }

    protected function configure(IPresenter $presenter): void {
        try {
            $this->setDataSource($this->getData());
        } catch (NotImplementedException $exception) {

        }
        $this->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.latte');
        /** @var GridPaginator $paginator */
        $paginator = $this->getComponent('paginator');
        $paginator->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.paginator.latte');
    }

    /**
     * @return IDataSource
     * @throws NotImplementedException
     */
    protected function getData(): IDataSource {
        throw new NotImplementedException();
    }

    /**
     * @return ITemplate
     * @throws BadTypeException
     */
    protected function createTemplate(): ITemplate {
        $presenter = $this->getPresenter();
        if (!$presenter instanceof BasePresenter) {
            throw new BadTypeException(BasePresenter::class, $presenter);
        }
        /**
         * @var GridPaginator $paginator
         * @var Template $template
         */
        $paginator = $this->getComponent('paginator');
        $paginator->getTemplate()->setTranslator($this->getTranslator());
        $template = parent::createTemplate();
        $template->setTranslator($this->getTranslator());
        return $template;
    }

    /*     * *****************************
     * Extended rendering for the paginator
     * ***************************** */
    /**
     * @throws GridException
     */
    public function render(): void {
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
            $steps = [$page];
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

    public function isSearchable(): bool {
        return $this->dataSource instanceof SearchableDataSource;
    }

    /**
     * @return FormControl
     * @throws BadTypeException
     */
    protected function createComponentSearchForm(): FormControl {
        if (!$this->isSearchable()) {
            throw new InvalidStateException("Cannot create search form without searchable data source.");
        }
        $control = new FormControl($this->getContext());
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
     * @param string|null $label
     * @return Button
     * @throws DuplicateButtonException
     */
    protected function addButton($name, ?string $label = null): Button {
        $button = parent::addButton($name, $label);
        $button->setClass('btn btn-sm btn-secondary');
        return $button;
    }

    /**
     * @param string $name
     * @param string|null $label
     * @return GlobalButton
     * @throws DuplicateGlobalButtonException
     * @deprecated do not use for links!
     */
    public function addGlobalButton($name, ?string $label = null): GlobalButton {
        $button = parent::addGlobalButton($name, $label);
        $button->setClass('btn btn-sm btn-primary');
        return $button;
    }

    /**
     * @param string $field
     * @param int $userPermission
     * @return Column
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    private function addReflectionColumn(string $field, int $userPermission): Column {
        $factory = $this->tableReflectionFactory->loadColumnFactory($field);
        return $this->addColumn(str_replace('.', '__', $field), $factory->getTitle())->setRenderer(function ($model) use ($factory, $userPermission): Html {
            if (!$model instanceof AbstractModelSingle) {
                $model = $this->getModelClassName()::createFromActiveRow($model);
            }
            return $factory->render($model, $userPermission);
        })->setSortable(false);
    }

    /**
     * @param string $factoryName
     * @param callable $accessCallback
     * @return Column
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function addJoinedColumn(string $factoryName, callable $accessCallback): Column {
        $factory = $this->tableReflectionFactory->loadColumnFactory($factoryName);
        return $this->addColumn(str_replace('.', '__', $factoryName), $factory->getTitle())->setRenderer(function ($row) use ($factory, $accessCallback) {
            $model = $accessCallback($row);
            return $factory->render($model, 1);
        });
    }

    /**
     * @return string|AbstractModelSingle
     * @throws NotImplementedException
     */
    protected function getModelClassName(): string {
        throw new NotImplementedException('Model className must be defined, if data source is not TypedSelection.');
    }

    /**
     * @param array $fields
     * @param int $userPermissions
     * @return void
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function addColumns(array $fields, int $userPermissions = FieldLevelPermission::ALLOW_FULL): void {
        foreach ($fields as $name) {
            $this->addReflectionColumn($name, $userPermissions);
        }
    }

    /**
     * @param string $destination
     * @param string $id
     * @param string $label
     * @param bool $checkACL
     * @param array $params
     * @return Button
     * @throws DuplicateButtonException
     */
    protected function addLinkButton(string $destination, string $id, string $label, bool $checkACL = true, array $params = []): Button {
        $paramMapCallback = function ($model) use ($params): array {
            $hrefParams = [];
            foreach ($params as $key => $value) {
                $hrefParams[$key] = $model->{$value};
            }
            return $hrefParams;
        };
        /** @var Button $button */
        $button = $this->addButton($id, $label)
            ->setText($label)
            ->setLink(function ($model) use ($destination, $paramMapCallback): string {
                if (!$model instanceof AbstractModelSingle) {
                    $model = $this->getModelClassName()::createFromActiveRow($model);
                }
                return $this->getPresenter()->link($destination, $paramMapCallback($model));
            });
        if ($checkACL) {
            $button->setShow(function ($model) use ($destination, $paramMapCallback): bool {
                if (!$model instanceof AbstractModelSingle) {
                    $model = $this->getModelClassName()::createFromActiveRow($model);
                }
                return $this->getPresenter()->authorized($destination, $paramMapCallback($model));
            });
        }
        return $button;
    }

    /**
     * @param string $linkId
     * @param bool $checkACL
     * @return Button
     * @throws BadTypeException
     * @throws DuplicateButtonException
     */
    protected function addLink(string $linkId, bool $checkACL = false): Button {
        $factory = $this->tableReflectionFactory->loadLinkFactory($linkId);
        /** @var Button $button */
        $button = $this->addButton(str_replace('.', '_', $linkId), $factory->getText())
            ->setText($factory->getText())
            ->setLink(function ($model) use ($factory): string {
                if (!$model instanceof AbstractModelSingle) {
                    $model = $this->getModelClassName()::createFromActiveRow($model);
                }
                return $factory->create($this->getPresenter(), $model);
            });
        if ($checkACL) {
            $button->setShow(function ($model) use ($factory) {
                if (!$model instanceof AbstractModelSingle) {
                    $model = $this->getModelClassName()::createFromActiveRow($model);
                }
                return $this->getPresenter()->authorized(...$factory->createLinkParameters($model));
            });
        }
        return $button;
    }

    /**
     * @return GlobalButton|Button
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
    public function handleCsv(): void {
        $columns = $this['columns']->components;
        $rows = $this->dataSource->getData();
        $data = [];
        foreach ($rows as $row) {
            $datum = [];
            /** @var Column $column */
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

    protected function getContext(): Container {
        return $this->container;
    }
}
