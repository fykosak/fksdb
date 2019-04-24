<?php

namespace FKSDB\Components\Grids\Events\Application;

use Closure;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Database\Table\Selection;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class AbstractApplicationGrid
 * @package FKSDB\Components\Grids\Events\Application
 */
abstract class AbstractApplicationGrid extends BaseGrid {
    /**
     * @var ModelEvent
     */
    protected $event;

    /**
     * AbstractApplicationGrid constructor.
     * @param ModelEvent $event
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(ModelEvent $event, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);
        $this->event = $event;
    }

    /**
     * @return Selection
     */
    abstract protected function getSource(): Selection;

    /**
     * @return FormControl
     * @throws \Nette\Application\BadRequestException
     */
    protected function createComponentSearchForm(): FormControl {
        $query = $this->getSource()->select('count(*) AS count,status.*')->group('status');

        $states = [];
        foreach ($query as $row) {
            $states[] = [
                'state' => $row->status,
                'count' => $row->count,
                'description' => $row->description,
            ];
        }

        $control = new FormControl();
        $form = $control->getForm();
        $stateContainer = new ContainerWithOptions();
        $stateContainer->setOption('label', _('States'));
        foreach ($states as $state) {
            $label = Html::el('span')
                ->addHtml(Html::el('b')->addText($state['state']))
                ->addText(': ')
                ->addHtml(Html::el('i')->addText(_($state['description'])))
                ->addText(' (' . $state['count'] . ')');
            $stateContainer->addCheckbox(\str_replace('.', '__', $state['state']), $label);
        }
        $form->addComponent($stateContainer, 'status');
        $form->addSubmit('submit', _('Apply'));
        $form->onSuccess[] = function (Form $form) {
            $values = $form->getValues();
            $this->searchTerm = $values;
            $this->dataSource->applyFilter($values);
            $count = $this->dataSource->getCount();
            $this->getPaginator()->itemCount = $count;
        };
        return $control;
    }

    /**
     * @return Closure
     */
    public function getFilterCallBack(): Closure {
        return function (Selection $table, $value) {
            $states = [];
            foreach ($value->status as $state => $value) {
                if ($value) {
                    $states[] = \str_replace('__', '.', $state);
                }
            }
            if (\count($states)) {
                $table->where('status IN ?', $states);
            }
        };
    }

    /**
     * @return array
     */
    abstract protected function getHoldersColumns(): array;

    /**
     * @param array $fields
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function addColumns(array $fields) {
        parent::addColumns($fields);

        $holderFields = $this->event->getHolder()->getPrimaryHolder()->getFields();

        foreach ($holderFields as $name => $def) {
            if (\in_array($name, $this->getHoldersColumns())) {
                $this->addReflectionColumn($this->getTableName(), $name, $this->getModelClassName());
            }
        }
    }
}
