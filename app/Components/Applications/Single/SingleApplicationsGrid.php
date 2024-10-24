<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Single;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends BaseGrid<EventParticipantModel,array{
 *     status?:string,
 * }>
 */
class SingleApplicationsGrid extends BaseGrid
{
    protected EventModel $event;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @phpstan-return string[]
     */
    protected function getHoldersColumns(): array
    {
        switch ($this->event->event_type_id) {
            case 2:
            case 14:
                return [];
        }
        return [
            'price',
            //'lunch_count',
            //'tshirt_color',
            //'tshirt_size',
            //'jumper_size',
            //'arrival_ticket',
            //'arrival_time',
            //'arrival_destination',
            //'departure_time',
            //'departure_ticket',
            //'departure_destination',
            'health_restrictions',
            'diet',
            'used_drugs',
            'note',
            'swimmer',
        ];
    }

    protected function addHolderColumns(): void
    {
        $fields = [];
        foreach ($this->getHoldersColumns() as $name) {
            $fields[] = '@' . DbNames::TAB_EVENT_PARTICIPANT . '.' . $name;
        }
        $this->addSimpleReferencedColumns($fields);
    }

    /**
     * @phpstan-return TypedGroupedSelection<EventParticipantModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        $query = $this->event->getParticipants();
        /** @var string $key */
        foreach ($this->filterParams as $key => $filterParam) {
            if (!$filterParam) {
                continue;
            }
            switch ($key) {
                case 'status':
                    $query->where('event_participant.status', $filterParam);
                    break;
                case 'code':
                    try {
                        /** @var PersonModel $model */
                        $model = MachineCode::parseModelHash($this->container, $filterParam, $this->event->getSalt());
                        $query->where('event_participant.person_id', $model->person_id);
                    } catch (\Throwable $exception) {
                    }
                    break;
            }
        }
        return $query;
    }

    protected function configure(): void
    {
        $this->filtered = true;
        $this->paginate = false;
        $this->addSimpleReferencedColumns([
            '@person.full_name',
            '@event_participant.status',
        ]);
        $this->addPresenterButton(
            'detail',
            'detail',
            new Title(null, _('button.detail')),
            false,
            ['id' => 'event_participant_id']
        );
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
        $form->addText('code', _('Code'));
    }
}
