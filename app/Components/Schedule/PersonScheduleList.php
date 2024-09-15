<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule;

use FKSDB\Components\Grids\Components\BaseList;
use FKSDB\Components\Grids\Components\Button\Button;
use FKSDB\Components\Grids\Components\Referenced\SimpleItem;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Components\Grids\Components\Table\RelatedTable;
use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\UI\EventRolePrinter;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends BaseList<PersonModel,array{code?:string}>
 */
final class PersonScheduleList extends BaseList
{
    private PersonService $personService;
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container, FieldLevelPermission::ALLOW_FULL);
        $this->event = $event;
    }

    public function injectServices(PersonService $personService): void
    {
        $this->personService = $personService;
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('code', _('Code'));
    }

    /**
     * @phpstan-return TypedSelection<PersonModel>
     */
    protected function getModels(): TypedSelection
    {
        $query = $this->personService->getTable()->where(
            ':person_schedule.schedule_item.schedule_group.event_id',
            $this->event->event_id
        )->group('person_id');
        try {
            if (isset($this->filterParams['code'])) {
                $model = MachineCode::parseModelHash(
                    $this->container,
                    $this->filterParams['code'],
                    $this->event->getSalt()
                );
                if ($model instanceof PersonModel) {
                    $query->where('person.person_id', $model->person_id);
                } elseif ($model instanceof TeamModel2) {
                    $query->where(
                        'person.person_id',
                        array_map(fn(PersonModel $person) => $person->person_id, $model->getPersons())
                    );
                }
            }
        } catch (\Throwable $exception) {
            $this->flashMessage(_('Cannot parse code'), Message::LVL_ERROR);
        }

        return $query;
    }

    protected function configure(): void
    {
        $this->paginate = true;
        $this->filtered = true;
        $this->mode = self::ModePanel;
        $this->setTitle(new SimpleItem($this->getContext(), '@person.full_name'));// @phpstan-ignore-line
        $row0 = $this->createRow();
        $row0->addComponent(
            new RendererItem(
                $this->container,
                fn(PersonModel $person) => EventRolePrinter::getHtml($person, $this->event),
                new Title(null, _('Role'))
            ),
            'role'
        );
        $row1 = $this->createRow();
        /** @phpstan-var RelatedTable<PersonModel,PersonScheduleModel> $relatedTable */
        $relatedTable = new RelatedTable(
            $this->container,
            fn(PersonModel $person) => $person->getScheduleForEvent($this->event),  //@phpstan-ignore-line
            new Title(null, '')
        );
        $row1->addComponent($relatedTable, 'schedule');
        /** @phpstan-ignore-next-line */
        $relatedTable->addTableColumn(
        /** @phpstan-ignore-next-line */
            new SimpleItem($this->container, '@schedule_item.name'),
            'item'
        );
        /** @phpstan-ignore-next-line */
        $relatedTable->addTableColumn(
        /** @phpstan-ignore-next-line */
            new SimpleItem($this->container, '@schedule_group.name'),
            'group'
        );
        /** @phpstan-ignore-next-line */
        $relatedTable->addTableColumn(
        /** @phpstan-ignore-next-line */
            new TemplateItem($this->container, '@schedule_item.price_czk/@schedule_item.price_eur', _('Price')),
            'price'
        );
        /** @phpstan-ignore-next-line */
        $relatedTable->addTableColumn(
        /** @phpstan-ignore-next-line */
            new SimpleItem($this->container, '@payment.payment'),
            'payment'
        );
        /** @phpstan-ignore-next-line */
        $relatedTable->addTableColumn(
        /** @phpstan-ignore-next-line */
            new SimpleItem($this->container, '@person_schedule.state'),
            'state'
        );
        /** @phpstan-ignore-next-line */
        $relatedTable->addTableButton(
        /** @phpstan-ignore-next-line */
            new Button(
                $this->container,
                $this->getPresenter(),
                new Title(null, _('Detail')),
                fn(PersonScheduleModel $model) => [':Schedule:Person:detail', ['id' => $model->person_schedule_id]]
            ),
            'detail'
        );
        /** @phpstan-ignore-next-line */
        $relatedTable->addTableButton(
        /** @phpstan-ignore-next-line */
            new Button(
                $this->container,
                $this->getPresenter(),
                new Title(null, _('Edit')),
                fn(PersonScheduleModel $model) => [':Schedule:Person:edit', ['id' => $model->person_schedule_id]]
            ),
            'edit'
        );
    }
}
