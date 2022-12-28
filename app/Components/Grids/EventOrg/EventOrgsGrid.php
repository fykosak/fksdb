<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\EventOrg;

use FKSDB\Components\Grids\RelatedGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

class EventOrgsGrid extends RelatedGrid
{

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container, $event, 'event_org');
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
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
