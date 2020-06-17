<?php

namespace OrgModule;

use FKSDB\Components\Controls\Entity\Event\EventForm;
use FKSDB\Components\Grids\Events\EventsGrid;
use FKSDB\EntityTrait;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use FKSDB\Exceptions\NotImplementedException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * @author Michal Koutný <michal@fykos.cz>
 * @method ModelEvent getEntity()
 */
class EventPresenter extends BasePresenter {
    use EntityTrait;

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * @param ServiceEvent $serviceEvent
     * @return void
     */
    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    public function titleList() {
        $this->setTitle(_('Events'), 'fa fa-calendar-check-o');
    }

    public function titleCreate() {
        $this->setTitle(_('Add event'), 'fa fa-calendar-plus-o');
    }

    /**
     * @return void
     */
    public function titleEdit() {
        $this->setTitle(sprintf(_('Edit event %s'), $this->getEntity()->name), 'fa fa-pencil');
    }

    /**
     * @throws NotImplementedException
     */
    public function actionDelete() {
        throw new NotImplementedException();
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function actionEdit() {
        $this->traitActionEdit();
    }

    /**
     * @return EventsGrid
     * @throws BadRequestException
     */
    protected function createComponentGrid(): EventsGrid {
        return new EventsGrid($this->getContext(), $this->getSelectedContest(), $this->getSelectedYear());
    }

    /**
     * @return Control
     * @throws BadRequestException
     */
    protected function createComponentCreateForm(): Control {
        return new EventForm($this->getSelectedContest(), $this->getContext(), $this->getSelectedYear(), true);
    }

    /**
     * @return Control
     * @throws BadRequestException
     */
    protected function createComponentEditForm(): Control {
        return new EventForm($this->getSelectedContest(), $this->getContext(), $this->getSelectedYear(), false);
    }

    protected function getORMService(): ServiceEvent {
        return $this->serviceEvent;
    }

    /**
     * @param $resource
     * @param string $privilege
     * @return bool
     * @throws BadRequestException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->getContestAuthorizator()->isAllowed($resource, $privilege, $this->getSelectedContest());
    }
}
