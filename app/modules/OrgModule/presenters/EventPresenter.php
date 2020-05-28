<?php

namespace OrgModule;

use FKSDB\Components\Controls\Entity\Event\CreateForm;
use FKSDB\Components\Controls\Entity\Event\EditForm;
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
     * @throws BadRequestException
     */
    public function actionEdit() {
        $this->traitActionEdit();
    }

    protected function createComponentGrid(): EventsGrid {
        return new EventsGrid($this->getContext());
    }

    /**
     * @inheritDoc
     */
    public function createComponentCreateForm(): Control {
        return new CreateForm($this->getContext(), $this->getSelectedContest(), $this->getSelectedYear());
    }

    /**
     * @inheritDoc
     */
    public function createComponentEditForm(): Control {
        return new EditForm($this->getContext(), $this->getSelectedContest());
    }

    /**
     * @inheritDoc
     */
    protected function getORMService() {
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
