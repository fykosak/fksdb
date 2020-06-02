<?php

namespace FKSDB\Components\Grids;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventOrg;
use FKSDB\ORM\Services\ServiceEventOrg;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

/**
 * Class EventOrgsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventOrgsGrid extends BaseGrid {

    private ServiceEventOrg $serviceEventOrg;

    private ModelEvent $event;

    /**
     * EventOrgsGrid constructor.
     * @param ModelEvent $event
     * @param Container $container
     */
    public function __construct(ModelEvent $event, Container $container) {
        parent::__construct($container);
        $this->event = $event;
    }

    public function injectServiceEventOrg(ServiceEventOrg $serviceEventOrg): void {
        $this->serviceEventOrg = $serviceEventOrg;
    }

    protected function getData(): IDataSource {
        $query = $this->serviceEventOrg->findByEvent($this->event);
        return new NDataSource($query);
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     * @throws NotImplementedException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);

        $this->addColumns(['person.full_name']);
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

    protected function getModelClassName(): string {
        return ModelEventOrg::class;
    }
}
