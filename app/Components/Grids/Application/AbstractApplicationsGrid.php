<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\SQL\SearchableDataSource;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Utils\Html;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateColumnException;

abstract class AbstractApplicationsGrid extends BaseGrid
{

    protected ModelEvent $event;
    private Holder $holder;

    public function __construct(ModelEvent $event, Holder $holder, Container $container)
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
     * @return FormControl
     * @throws BadTypeException
     */
    protected function createComponentSearchForm(): FormControl
    {
        $query = $this->getSource()->select('count(*) AS count,status.*')->group('status');

        $states = [];
        foreach ($query as $row) {
            $states[] = [
                'state' => $row->status,
                'count' => $row->count,
                'description' => $row->description,
            ];
        }

        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $stateContainer = new ContainerWithOptions($this->getContext());
        $stateContainer->setOption('label', _('States'));
        foreach ($states as $state) {
            $label = Html::el('span')
                ->addHtml(Html::el('b')->addText($state['state']))
                ->addText(': ')
                ->addHtml(Html::el('i')->addText(_((string)$state['description'])))
                ->addText(' (' . $state['count'] . ')');
            $stateContainer->addCheckbox(str_replace('.', '__', $state['state']), $label);
        }
        $form->addComponent($stateContainer, 'status');
        $form->addSubmit('submit', _('Apply filter'));
        $form->onSuccess[] = function (Form $form): void {
            $values = $form->getValues('array');
            $this->searchTerm = $values;
            $this->dataSource->applyFilter($values);
            $count = $this->dataSource->getCount();
            $this->getPaginator()->itemCount = $count;
        };
        return $control;
    }

    /**
     * @param Presenter $presenter
     * @return void
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
     * @return void
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function addHolderColumns(): void
    {
        $holderFields = $this->holder->getPrimaryHolder()->getFields();
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
