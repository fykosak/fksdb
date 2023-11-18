<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\EventOrganizer;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<EventOrganizerModel,array{}>
 */
final class EventOrganizersGrid extends BaseGrid
{
    private EventModel $event;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @phpstan-return TypedGroupedSelection<EventOrganizerModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->event->getEventOrganizers();
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->paginate = false;
        $this->counter = true;
        $this->filtered = false;
        $this->addSimpleReferencedColumns([
            '@person.full_name',
            '@event_org.note',
        ]);
        $this->addPresenterButton('edit', 'edit', new Title(null, _('button.edit')), false, ['id' => 'e_org_id']);
        $this->addPresenterButton('detail', 'detail', new Title(null, _('button.detail')), false, ['id' => 'e_org_id']);
        //  $this->addLinkButton('delete','delete',_('Delete'),false,['id' => 'e_org_id']);
    }
}
