<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Grids\Components\FilterGrid;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\Forms\Form;

class SingleApplicationsGrid extends FilterGrid
{
    protected EventModel $event;
    private BaseHolder $holder;

    public function __construct(EventModel $event, BaseHolder $holder, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
        $this->holder = $holder;
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

    protected function getModels(): Selection
    {
        $query = $this->event->getParticipants();
        if (!isset($this->filterParams)) {
            return $query;
        }
        foreach ($this->filterParams as $key => $filterParam) {
            switch ($key) {
                case 'status':
                    $query->where('event_participant.status', $filterParam);
            }
        }
        return $query;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->paginate = false;
        $this->addColumns([
            'person.full_name',
            'event_participant.status',
        ]);
        $this->addPresenterButton('detail', 'detail', _('Detail'), false, ['id' => 'event_participant_id']);
        // $this->addCSVDownloadButton();
        $this->addHolderColumns();
    }

    protected function configureForm(Form $form): void
    {
        $items = [];
        foreach (EventParticipantStatus::cases() as $state) {
            $items[$state->value] = $state->label();
        }
        $form->addSelect('status', _('Status'), $items)->setPrompt(_('Select state'));
    }
}
