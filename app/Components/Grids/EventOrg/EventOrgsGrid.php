<?php

namespace FKSDB\Components\Grids\EventOrg;

use FKSDB\Components\Grids\RelatedGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventOrg;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class EventOrgsGrid extends RelatedGrid
{

    public function __construct(ModelEvent $event, Container $container)
    {
        parent::__construct($container, $event, 'event_org');
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->addColumns([
            'person.full_name',
            'event_org.note',
        ]);
        $this->addLink('event_org.edit');
        //  $this->addLinkButton('edit', 'edit', _('Edit'), false, ['id' => 'e_org_id']);
        // $this->addLinkButton('detail', 'detail', _('Detail'), false, ['id' => 'e_org_id']);
        //  $this->addLinkButton('delete','delete',_('Delete'),false,['id' => 'e_org_id']);
    }

    protected function getModelClassName(): string
    {
        return ModelEventOrg::class;
    }
}
