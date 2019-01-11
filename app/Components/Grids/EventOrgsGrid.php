<?php

namespace FKSDB\Components\Grids;

use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventOrg;
use ServiceEventOrg;
use SQL\SearchableDataSource;

class EventOrgsGrid extends BaseGrid {

    /**
     * @var ServiceEventOrg
     */
    private $serviceEventOrg;
    /**
     * @var ModelEvent
     */
    private $event;

    function __construct(ModelEvent $event, ServiceEventOrg $serviceEventOrg) {
        parent::__construct();
        $this->event = $event;
        $this->serviceEventOrg = $serviceEventOrg;
    }

    /**
     * @param \AuthenticatedPresenter $presenter
     * @throws \Nette\Application\BadRequestException
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \NiftyGrid\DuplicateGlobalButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $orgs = $this->serviceEventOrg->findByEventId($this->event);

        $dataSource = new SearchableDataSource($orgs);
        $this->setDataSource($dataSource);
        $this->addColumn('display_name', _('Jméno'))->setRenderer(function ($row) {
            $eventOrg = ModelEventOrg::createFromTableRow($row);
            return $eventOrg->getPerson()->getFullName();
        });
        $this->addColumn('note', _('Note'));
        $this->addButton('edit', _('Edit'))->setText(_('Edit'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('edit', $row->e_org_id);
            })
            ->setShow(function ($row) use ($presenter) {
                return $presenter->authorized('edit', ['id' => $row->e_org_id]);
            });
        $this->addButton('delete', _('Smazat'))->setClass('btn btn-sm btn-danger')->setText(_('Smazat'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('delete', $row->e_org_id);
            })
            ->setShow(function ($row) use ($presenter) {
                return $presenter->authorized('delete', ['id' => $row->e_org_id]);
            })
            ->setConfirmationDialog(function () {
                return _('Opravdu smazat organizátora?');
            });

        if ($presenter->authorized('create')) {
            $this->addGlobalButton('add')
                ->setLabel(_('Přidat organizátora'))
                ->setLink($this->getPresenter()->link('create'));
        }

    }

}
