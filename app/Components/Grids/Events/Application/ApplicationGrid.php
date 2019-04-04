<?php

namespace FKSDB\Components\Grids\Events;

use Closure;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Helpers\ValuePrinters\BinaryValueControl;
use FKSDB\Components\Controls\Helpers\ValuePrinters\PersonValueControl;
use FKSDB\Components\Controls\Helpers\ValuePrinters\PriceValueControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Grids\BaseGrid;
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
class ApplicationGrid extends BaseGrid {
    /**
     * @var ModelEvent
     */
    protected $event;

    /**
     * ParticipantGrid constructor.
     * @param ModelEvent $event
     */
    public function __construct(ModelEvent $event) {
        parent::__construct();
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
            return PersonValueControl::getGridValue($this, $model->getPerson(), $model->getEvent()->year, $model->getEvent()->getEventType()->contest_id);
        });

        $this->addColumn('status', _('Status'))->setRenderer(function ($row) use ($presenter) {
            $model = ModelEventParticipant::createFromTableRow($row);
            switch ($model->status) {
                case'participated':
                    $className = 'badge badge-success';
                    break;
                case 'applied':
                case 'applied.nodsef':
                case 'applied.notsaf':
                case 'applied.tsaf':
                case 'approved':
                case 'paid':
                    $className = 'badge badge-primary';
                    break;
                case 'interested':
                    $className = 'badge badge-info';
                    break;
                case 'rejected':
                case 'missed':
                case 'cancelled':
                    $className = 'badge badge-danger';
                    break;
                case 'out_of_db':
                    $className = 'badge badge-light';
                    break;
                case 'auto.invited':
                case 'invited':
                case 'invited1':
                case 'invited2':
                case 'invited3':
                case 'auto.spare':
                case 'spare':
                case 'spare1':
                case 'spare2':
                case 'spare3':
                case 'spare.tsaf':
                case 'pending':
                    $className = 'badge badge-warning';
                    break;
                case 'disqualified':
                    $className = 'badge badge-dark';
                    break;
                default:
                    $className = 'badge badge-secondary';
            }
            return Html::el('span')
                ->addAttributes(['class' => $className])
                ->addText(_($model->status));
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
     */
    private function addColumns() {
        $fields = $this->event->getHolder()->getPrimaryHolder()->getFields();

        foreach ($fields as $name => $def) {
            switch ($name) {
                case 'note':
                    $this->addColumn('note', _('Note'));
                    break;
                case 'diet':
                    $this->addColumn('diet', _('Diet'));
                    break;
                case 'health_restrictions':
                    $this->addColumn('health_restrictions', _('Health restrictions'));
                    break;
                case 'price':
                    $this->addColumn('price', _('Price'))->setRenderer(function ($row) {
                        $model = ModelEventParticipant::createFromTableRow($row);
                        return PriceValueControl::getGridValue($model);
                    });
                    break;
                case 'used_drugs':
                    $this->addColumn('used_drugs', _('Used drugs'));
                    break;
                case 'swimmer':
                    $this->addColumn('swimmer', _('Swimmer'))->setRenderer(function ($row) {
                        $model = ModelEventParticipant::createFromTableRow($row);
                        return BinaryValueControl::getGridValue($model, 'swimmer');
                    });
                    break;
                case 'tshirt_size':
                    $this->addColumn('tshirt_size', _('T-shirt size'));
                    break;
                case 'tshirt_color':
                    $this->addColumn('tshirt_color', _('T-shirt color'));
                    break;
                case 'arrival_destination':
                    $this->addColumn('arrival_destination', _('Arrival destination'));
                    break;
                case 'arrival_time':
                    $this->addColumn('arrival_time', _('Arrival time'));
                    break;
                case 'arrival_ticket':
                    $this->addColumn('arrival_ticket', _('Arrival ticket'))->setRenderer(function ($row) {
                        $model = ModelEventParticipant::createFromTableRow($row);
                        return BinaryValueControl::getGridValue($model, 'arrival_ticket');
                    });
                    break;
                case 'departure_destination':
                    $this->addColumn('departure_destination', _('Departure destination'));
                    break;
                case 'departure_time':
                    $this->addColumn('departure_time', _('Departure time'));
                    break;
                case 'departure_ticket':
                    $this->addColumn('departure_ticket', _('Departure ticket'))->setRenderer(function ($row) {
                        $model = ModelEventParticipant::createFromTableRow($row);
                        return BinaryValueControl::getGridValue($model, 'departure_ticket');
                    });
            }
        }

    }
}
