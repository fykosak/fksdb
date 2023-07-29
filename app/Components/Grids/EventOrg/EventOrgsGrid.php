<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\EventOrg;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventOrgModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\DI\Container;

class EventOrgsGrid extends BaseGrid
{
    private EventModel $event;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @phpstan-return TypedGroupedSelection<EventOrgModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->event->getEventOrgs();
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->addColumns([
            'person.full_name',
            'event_org.note',
        ]);
        $this->addORMLink('event_org.edit');
        //  $this->addLinkButton('edit', 'edit', _('Edit'), false, ['id' => 'e_org_id']);
        // $this->addLinkButton('detail', 'detail', _('Detail'), false, ['id' => 'e_org_id']);
        //  $this->addLinkButton('delete','delete',_('Delete'),false,['id' => 'e_org_id']);
    }
}
