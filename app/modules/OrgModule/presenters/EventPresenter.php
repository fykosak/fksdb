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
 * @method ModelEvent loadEntity(int $id)
 */
class EventPresenter extends BasePresenter {
    use EntityTrait;

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * @param ServiceEvent $serviceEvent
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
     * @param int $id
     * @throws BadRequestException
     */
    public function titleEdit(int $id) {
        $this->setTitle(sprintf(_('Edit event %s'), $this->loadEntity($id)->name), 'fa fa-pencil');
    }

    /**
     * @throws NotImplementedException
     */
    public function actionDelete() {
        throw new NotImplementedException;
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function actionEdit(int $id) {
        $this->traitActionEdit($id);
    }

    /**
     * @return EventsGrid
     */
    protected function createComponentGrid(): EventsGrid {
        return new EventsGrid($this->getContext());
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws \Exception
     */
    private function createForm() {
        $control = new FormControl();
        $form = $control->getForm();

        $eventContainer = $this->eventFactory->createEvent($this->getSelectedContest());
        $form->addComponent($eventContainer, self::CONT_EVENT);
        /** @var ModelEvent $event */
        $event = $this->getModel();
        if ($event) { // intentionally =
            /** @var Holder $holder */
            $holder = $this->container->createServiceEventHolder($event);
            $scheme = $holder->getPrimaryHolder()->getParamScheme();
            $paramControl = $eventContainer->getComponent('parameters');
            $paramControl->setOption('description', $this->createParamDescription($scheme));
            $paramControl->addRule(function (BaseControl $control) use ($scheme) {
                $parameters = $control->getValue();
                try {
                    if ($parameters) {
                        $parameters = Neon::decode($parameters);
                    } else {
                        $parameters = [];
                    }

                    NeonScheme::readSection($parameters, $scheme);
                    return true;
                } catch (NeonException $exception) {
                    $control->addError($exception->getMessage());
                    return false;
                }
            }, _('Parametry nesplňují Neon schéma'));
        }

        return $control;
    }

    /**
     * @param $scheme
     * @return Html
=======
     * @inheritDoc
>>>>>>> origin/master
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
