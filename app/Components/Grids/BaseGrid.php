<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\ORMFactory;
use FKSDB\Models\SQL\SearchableDataSource;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\NetteORM\Model;
use Nette\Application\UI\Form;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Template;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\Localization\Translator;
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

abstract class BaseGrid extends Grid
{

    /** @persistent string */
    public ?array $searchTerm = null;
    protected ORMFactory $tableReflectionFactory;

    private Container $container;

    public function __construct(Container $container)
    {
        parent::__construct();
        $this->container = $container;
        $container->callInjects($this);
    }

    final public function injectBase(ORMFactory $tableReflectionFactory, Translator $translator): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->setTranslator($translator);
    }

    protected function configure(Presenter $presenter): void
    {
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
     * @throws NotImplementedException
     */
    protected function getData(): IDataSource
    {
        throw new NotImplementedException();
    }

    /**
     * @throws BadTypeException
     */
    protected function createTemplate(): Template
    {
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
    public function render(): void
    {
        $paginator = $this->getPaginator();

        // this has to be done already here (and in the parent call again :-( )
        if (isset($this->searchTerm)) {
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

    public function isSearchable(): bool
    {
        return $this->dataSource instanceof SearchableDataSource;
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentSearchForm(): FormControl
    {
        if (!$this->isSearchable()) {
            throw new InvalidStateException('Cannot create search form without searchable data source.');
        }
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        //$form = new Form();
        $form->setMethod(Form::GET);
        $form->addText('term')
            ->setDefaultValue($this->searchTerm['term'])
            ->setHtmlAttribute('placeholder', _('Find'));
        $form->addSubmit('submit', _('Search'));
        $form->onSuccess[] = function (Form $form) {
            $values = $form->getValues('array');
            $this->searchTerm = $values;
            $this->dataSource->applyFilter($values);
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
     * @throws DuplicateButtonException
     */
    protected function addButton(string $name, ?string $label = null): Button
    {
        $button = parent::addButton($name, $label);
        $button->setClass('btn btn-sm btn-outline-secondary');
        return $button;
    }

    /**
     * @throws DuplicateGlobalButtonException
     */
    public function addGlobalButton(string $name, ?string $label = null): GlobalButton
    {
        $button = parent::addGlobalButton($name, $label);
        $button->setClass('btn btn-sm btn-outline-primary');
        return $button;
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    private function addReflectionColumn(string $field, int $userPermission): Column
    {
        $factory = $this->tableReflectionFactory->loadColumnFactory(...explode('.', $field));
        return $this->addColumn(str_replace('.', '__', $field), $factory->getTitle())->setRenderer(
            function ($model) use ($factory, $userPermission): Html {
                return $factory->render($model, $userPermission);
            }
        )->setSortable(false);
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function addJoinedColumn(string $factoryName, callable $accessCallback): Column
    {
        $factory = $this->tableReflectionFactory->loadColumnFactory(...explode('.', $factoryName));
        return $this->addColumn(str_replace('.', '__', $factoryName), $factory->getTitle())->setRenderer(
            function ($row) use ($factory, $accessCallback) {
                $model = $accessCallback($row);
                return $factory->render($model, 1);
            }
        );
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function addColumns(array $fields, int $userPermissions = FieldLevelPermission::ALLOW_FULL): void
    {
        foreach ($fields as $name) {
            $this->addReflectionColumn($name, $userPermissions);
        }
    }

    /**
     * @throws DuplicateButtonException
     */
    protected function addLinkButton(
        string $destination,
        string $id,
        string $label,
        bool $checkACL = true,
        array $params = []
    ): Button {
        $paramMapCallback = function ($model) use ($params): array {
            $hrefParams = [];
            foreach ($params as $key => $value) {
                $hrefParams[$key] = $model->{$value};
            }
            return $hrefParams;
        };
        $button = $this->addButton($id, $label)
            ->setText($label)
            ->setLink(fn(Model $model): string => $this->getPresenter()->link($destination, $paramMapCallback($model)));
        if ($checkACL) {
            $button->setShow(
                fn(Model $model): bool => $this->getPresenter()->authorized($destination, $paramMapCallback($model))
            );
        }
        return $button;
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateButtonException
     */
    protected function addLink(string $linkId, bool $checkACL = false): Button
    {
        $factory = $this->tableReflectionFactory->loadLinkFactory(...explode('.', $linkId, 2));
        $button = $this->addButton(str_replace('.', '_', $linkId), $factory->getText())
            ->setText($factory->getText())
            ->setLink(fn(Model $model): string => $factory->create($this->getPresenter(), $model));
        if ($checkACL) {
            $button->setShow(
                fn(Model $model) => $this->getPresenter()->authorized(...$factory->createLinkParameters($model))
            );
        }
        return $button;
    }

    /**
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     */
    protected function addCSVDownloadButton(): GlobalButton
    {
        return $this->addGlobalButton('csv', _('Download as csv'))
            ->setLink($this->link('csv!'));
    }

    public function handleCsv(): void
    {
        $columns = $this->getColumnsContainer()->components;
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

    protected function getContext(): Container
    {
        return $this->container;
    }
}
