<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Grids\FilterBaseGrid;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Utils\Html;

class SingleApplicationsGrid extends FilterBaseGrid
{

    protected EventModel $event;
    private BaseHolder $holder;

    public function __construct(EventModel $event, BaseHolder $holder, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
        $this->holder = $holder;
    }

    protected function getData(): TypedGroupedSelection
    {
        return $this->event->getParticipants()->order('person.family_name');
    }

    protected function getFilterCallBack(): void
    {
        $states = [];
        foreach ($this->searchTerm['status'] as $state => $value) {
            if ($value) {
                $states[] = str_replace('__', '.', $state);
            }
        }
        if (count($states)) {
            $this->data->where('status IN ?', $states);
        }
    }

    protected function getHoldersColumns(): array
    {
        return [
            'price',
            'lunch_count',
            'tshirt_color',
            'tshirt_size',
            //'jumper_size',
            'arrival_ticket',
            'arrival_time',
            'arrival_destination',
            'departure_time',
            'departure_ticket',
            'departure_destination',
            'health_restrictions',
            'diet',
            'used_drugs',
            'note',
            'swimmer',
        ];
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function addHolderColumns(): void
    {
        $holderFields = $this->holder->getFields();
        $fields = [];
        foreach ($holderFields as $name => $def) {
            if (in_array($name, $this->getHoldersColumns())) {
                $fields[] = DbNames::TAB_EVENT_PARTICIPANT . '.' . $name;
            }
        }
        $this->addColumns($fields);
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(Presenter $presenter): void
    {
        $this->paginate = false;

        $this->addColumns([
            'person.full_name',
            'event_participant.status',
        ]);
        $this->addPresenterButton('detail', 'detail', _('Detail'), false, ['id' => 'event_participant_id']);
        // $this->addCSVDownloadButton();
        $this->addHolderColumns();
        parent::configure($presenter);
    }

    protected function getStateCases(): array
    {
        $query = $this->data->select('count(*) AS count,status.*')->group('status');

        $states = [];
        foreach ($query as $row) {
            $states[] = [
                'state' => $row->status,
                'count' => $row->count,
                'description' => $row->description,
            ];
        }
        return $states;
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentSearchForm(): FormControl
    {
        $control = new FormControl($this->container);
        $form = $control->getForm();
        $stateContainer = new ContainerWithOptions($this->container);
        $stateContainer->setOption('label', _('States'));
        foreach ($this->getStateCases() as $state) {
            $label = Html::el('span')
                ->addHtml(Html::el('b')->addText($state['state']))
                ->addText(': ')
                ->addHtml(Html::el('i')->addText(_((string)$state['description'])))
                ->addText(' (' . $state['count'] . ')');
            $stateContainer->addCheckbox(str_replace('.', '__', $state['state']->value), $label);
        }
        $form->addComponent($stateContainer, 'status');
        $form->addSubmit('submit', _('Apply filter'));
        $form->onSuccess[] = function (Form $form): void {
            $this->searchTerm = $form->getValues('array');
        };
        return $control;
    }
}
