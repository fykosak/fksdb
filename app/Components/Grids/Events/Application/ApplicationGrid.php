<?php

namespace FKSDB\Components\Grids\Events;

use Closure;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\Events\Application\AbstractApplicationGrid;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use Nette\Forms\Form;
use Nette\Utils\Html;
use SQL\SearchableDataSource;

/**
 * Class ParticipantGrid
 * @package FKSDB\Components\Grids\Events
 */
class ApplicationGrid extends AbstractApplicationGrid {
    /**
     * @var ModelEvent
     */
    protected $event;

    /**
     * ParticipantGrid constructor.
     * @param ModelEvent $event
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(ModelEvent $event, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);
        $this->event = $event;
    }

    /**
     * @param Presenter $presenter
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \NiftyGrid\DuplicateButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $participants = $this->event->getParticipants();
        $this->paginate = false;

        $source = new SearchableDataSource($participants);
        $source->setFilterCallback($this->getFilterCallBack());
        $this->setDataSource($source);


        $this->addColumn('person_id', _('Person'))->setRenderer(function ($row) use ($presenter) {
            $model = ModelEventParticipant::createFromTableRow($row);
            // $factory = new PersonValueControlControl($presenter->getTranslator());
            // $factory->createGridItem($model->getPerson(), 'person_id');
            return Html::el('a')
                ->addAttributes(['href' => $this->getPresenter()->link(':Common:Stalking:view', [
                    'id' => $model->person_id,
                ])])
                ->addText($model->getPerson()->getFullName());
        });

        $factory = $this->tableReflectionFactory->loadService(DbNames::TAB_EVENT_PARTICIPANT, 'status');
        $this->addColumn('statue', $factory::getTitle())->setRenderer(function ($row) use ($factory) {
            $model = ModelEventParticipant::createFromTableRow($row);
            return $factory->renderValue($model, 'status', 1);
        });
        $this->addColumns();

        $this->addButton('detail')->setShow(function ($row) {
            $model = ModelEventParticipant::createFromTableRow($row);
            // return true;
            return !\in_array($model->getEvent()->event_type_id, [1, 9]);
        })->setText(_('Detail'))
            ->setLink(function ($row) {
                $model = ModelEventParticipant::createFromTableRow($row);
                return $this->getPresenter()->link('detail', [
                    'id' => $model->event_participant_id,
                ]);
            });
    }

    /**
     * @return FormControl
     * @throws \Nette\Application\BadRequestException
     */
    protected function createComponentSearchForm(): FormControl {
        // TODo from DB
        $states = [
            'participated',
            'applied',
            'applied.nodsef',
            'applied.notsaf',
            'applied.tsaf',
            'approved',
            'paid',
            'interested',
            'rejected',
            'missed',
            'cancelled',
            'out_of_db',
            'auto.invited',
            'invited',
            'invited1',
            'invited2',
            'invited3',
            'auto.spare',
            'spare',
            'spare1',
            'spare2',
            'spare3',
            'spare.tsaf',
            'pending',
            'disqualified',
        ];
        $control = new FormControl();
        $form = $control->getForm();
        $stateContainer = new ContainerWithOptions();
        $stateContainer->setOption('label', _('States'));
        foreach ($states as $state) {
            // TODO read default value from URL
            $stateContainer->addCheckbox(\str_replace('.', '__', $state), _($state));
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
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \Exception
     */
    private function addColumns() {
        $fields = $this->event->getHolder()->getPrimaryHolder()->getFields();

        foreach ($fields as $name => $def) {
            switch ($name) {
                case 'note':
                case 'swimmer':
                case 'arrival_ticket':
                case 'tshirt_color':
                case 'departure_time':
                case 'departure_ticket':
                case 'departure_destination':
                case 'arrival_time':
                case 'arrival_destination':
                case 'health_restrictions':
                case 'diet':
                case 'used_drugs':
                case 'tshirt_size':
                case 'price':
                    $this->addReflectionColumns(DbNames::TAB_EVENT_PARTICIPANT, $name, ModelEventParticipant::class);
            }
        }
    }
}
