<?php

namespace FKSDB\Components\Grids;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEventOrg;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class EventOrgsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
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
     * @param Presenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);

        $this->addLink('event_org.edit');
        //  $this->addLinkButton('edit', 'edit', _('Edit'), false, ['id' => 'e_org_id']);
        // $this->addLinkButton('detail', 'detail', _('Detail'), false, ['id' => 'e_org_id']);
        //  $this->addLinkButton('delete','delete',_('Delete'),false,['id' => 'e_org_id']);
    }
}
