<?php

namespace EventModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\LanguageChooser;
use FKSDB\ORM\ModelContest;
use FKSDB\ORM\ModelEvent;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use ServiceEvent;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class BasePresenter extends AuthenticatedPresenter {

    /**
     *
     * @var \FKSDB\ORM\ModelEvent
     */
    private $event;

    /**
     * @var int
     * @persistent
     */
    public $eventId;
    /**
     *
     * @var Container
     */
    protected $container;

    /**
     * @var ServiceEvent
     */
    protected $serviceEvent;

    public function injectContainer(Container $container) {
        $this->container = $container;
    }

    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    protected function createComponentLanguageChooser() {
        $control = new LanguageChooser($this->session);
        return $control;
    }

    /**
     * @throws BadRequestException
     * @throws BadRequestException
     */
    public function startup() {
        /**
         * @var $languageChooser LanguageChooser
         */
        $languageChooser = $this['languageChooser'];
        $languageChooser->syncRedirect();

        if (!$this->eventExist()) {
            throw new BadRequestException('Event not found.', 404);
        }
        parent::startup();
    }

    /**
     * @return bool
     * @throws BadRequestException
     */
    protected function eventExist() {
        return $this->getEvent() ? true : false;
    }

    public function getSubtitle() {
        return sprintf(_('Event "%s".'), $this->getEvent()->name);
    }

    /**
     * @return int
     * @throws BadRequestException
     */
    public function getEventId() {
        if (!$this->eventId) {
            throw new BadRequestException(\sprintf(_('Event id je povinné')));
        }
        return $this->eventId;
    }

    /**
     * @return ModelEvent|null
     * @throws BadRequestException
     */
    protected function getEvent() {
        if (!$this->event) {
            $row = $this->serviceEvent->findByPrimary($this->getEventId());
            if (!$row) {
                return null;
            }
            $this->event = ModelEvent::createFromTableRow($row);
            if ($this->event) {
                $holder = $this->container->createEventHolder($this->getEvent());
                $this->event->setHolder($holder);
            }
        }
        return $this->event;
    }

    protected function eventIsAllowed($resource, $privilege): bool {
        $event = $this->getEvent();
        if (!$event) {
            return false;
        }
        return $this->getEventAuthorizator()->isAllowed($resource, $privilege, $event);
    }

    protected function isContestsOrgAllowed($resource, $privilege): bool {
        $contest = $this->getContest();
        if (!$contest) {
            return false;
        }
        return $this->getContestAuthorizator()->isAllowed($resource, $privilege, $contest);
    }


    public function getNavBarVariant() {
        return ['event event-type-' . $this->getEvent()->event_type_id, ($this->getEvent()->event_type_id == 1) ? 'dark' : 'light'];
    }

    public function getNavRoot() {
        return 'event.dashboard.default';
    }

    protected function getContest(): ModelContest {
        return $this->getEvent()->getContest();
    }

}
