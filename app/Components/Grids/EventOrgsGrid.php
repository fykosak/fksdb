<?php

namespace FKSDB\Components\Grids;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventOrg;
use FKSDB\ORM\Services\ServiceEventOrg;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

/**
 * Class EventOrgsGrid
 * @package FKSDB\Components\Grids
 */
class EventOrgsGrid extends BaseGrid {

    /**
     * @var ServiceEventOrg
     */
    private $serviceEventOrg;
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * EventOrgsGrid constructor.
     * @param ModelEvent $event
     * @param ServiceEventOrg $serviceEventOrg
     * @param TableReflectionFactory $tableReflectionFactory
     */
    function __construct(ModelEvent $event, ServiceEventOrg $serviceEventOrg, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);
        $this->event = $event;
        $this->serviceEventOrg = $serviceEventOrg;
    }

    /**
     * @param \AuthenticatedPresenter $presenter
     * @throws BadRequestException
     * @throws InvalidLinkException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $orgs = $this->serviceEventOrg->findByEvent($this->event);

        $dataSource = new NDataSource($orgs);
        $this->setDataSource($dataSource);
        $this->addColumns(['referenced.person_name']);
        $this->addColumn('note', _('Note'));
        $this->addButton('edit', _('Edit'))->setText(_('Edit'))
            ->setLink(function (ModelEventOrg $model) {
                return $this->getPresenter()->link(':Org:EventOrg:edit', [
                    'id' => $model->e_org_id,
                    'contestId' => $model->getEvent()->getEventType()->contest_id,
                    'year' => $model->getEvent()->year,
                    'eventId' => $model->getEvent()->event_id,
                ]);
            });

        $this->addButton('delete')->setText(_('Delete'))
            ->setLink(function (ModelEventOrg $model) {
                return $this->getPresenter()->link('delete', $model->getPrimary());
            });

        if ($this->getPresenter()->authorized('create')) {
            $this->addGlobalButton('create')
                ->setLabel(_('Add organiser'))
                ->setLink($this->getPresenter()->link(':Org:EventOrg:create'));
        }
    }

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelEventOrg::class;
    }
}
