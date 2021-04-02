<?php

namespace FKSDB\Components\Grids\EventOrg;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\Application\IPresenter;
use FKSDB\Models\ORM\Services\ServiceEventOrg;
use Nette\DI\Container;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class EventOrgsGrid extends EntityGrid {

    public function __construct(ModelEvent $event, Container $container) {
        parent::__construct($container, ServiceEventOrg::class, [
            'person.full_name',
            'event_org.note',
        ], [
            'event_id' => $event->event_id,
        ]);
    }

    /**
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(IPresenter $presenter): void {
        parent::configure($presenter);

        $this->addLink('event_org.edit');
        //  $this->addLinkButton('edit', 'edit', _('Edit'), false, ['id' => 'e_org_id']);
        // $this->addLinkButton('detail', 'detail', _('Detail'), false, ['id' => 'e_org_id']);
        //  $this->addLinkButton('delete','delete',_('Delete'),false,['id' => 'e_org_id']);
    }
}
