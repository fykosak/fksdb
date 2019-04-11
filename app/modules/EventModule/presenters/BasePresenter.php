<?php

namespace EventModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\LanguageChooser;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceContestYear;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\YearCalculator;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class BasePresenter extends AuthenticatedPresenter {
    /**
     * @var TableReflectionFactory
     */
    protected $tableReflectionFactory;
    /**
     *
     * @var \FKSDB\ORM\Models\ModelEvent
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
     * @var \FKSDB\ORM\Services\ServiceEvent
     */
    protected $serviceEvent;

    /**
     * @var ServiceContestYear
     */
    protected $serviceContestYear;

    /**
     * @param ServiceContestYear $serviceContestYear
     */
    public function injectServiceContestYear(ServiceContestYear $serviceContestYear) {
        $this->serviceContestYear = $serviceContestYear;
    }

    /**
     * @var YearCalculator
     */
    protected $yearCalculator;

    /**
     * @param \FKSDB\YearCalculator $yearCalculator
     */
    public function injectYearCalculator(YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @param Container $container
     */
    public function injectContainer(Container $container) {
        $this->container = $container;
    }

    /**
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function injectTableReflectionFactory(TableReflectionFactory $tableReflectionFactory) {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @param ServiceEvent $serviceEvent
     */
    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    /**+
     * @return LanguageChooser
     */
    protected function createComponentLanguageChooser(): LanguageChooser {
        return new LanguageChooser($this->session);
    }

    /**
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function startup() {
        /**
         * @var LanguageChooser $languageChooser
         */
        $languageChooser = $this->getComponent('languageChooser');
        $languageChooser->syncRedirect();

        if (!$this->eventExist()) {
            throw new BadRequestException('Event not found.', 404);
        }
        parent::startup();
    }

    /**
     * @return bool
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function eventExist(): bool {
        return !!$this->getEvent();
    }

    /**
     * @return int
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function getAcYear(): int {
        return $this->yearCalculator->getAcademicYear($this->getEvent()->getContest(), $this->getEvent()->year);
    }

    /**
     * @return string
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function getSubTitle(): string {
        return $this->getEvent()->__toString();
    }

    /**
     * @return int
     * @throws \Nette\Application\AbortException
     */
    protected function getEventId(): int {
        if (!$this->eventId) {
            $this->redirect('Dispatch:default');
        }
        return +$this->eventId;
    }

    /**
     * @return \FKSDB\ORM\Models\ModelEvent
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function getEvent(): ModelEvent {
        if (!$this->event) {
            $row = $this->serviceEvent->findByPrimary($this->getEventId());
            if (!$row) {
                throw new BadRequestException('Event not found');
            }
            $this->event = ModelEvent::createFromTableRow($row);
            if ($this->event) {
                $holder = $this->container->createEventHolder($this->getEvent());
                $this->event->setHolder($holder);
            }
        }
        return $this->event;
    }

    /**
     * @param $resource
     * @param $privilege
     * @return bool
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function eventIsAllowed($resource, $privilege): bool {
        $event = $this->getEvent();
        if (!$event) {
            return false;
        }
        return $this->getEventAuthorizator()->isAllowed($resource, $privilege, $event);
    }

    /**
     * @param $resource
     * @param $privilege
     * @return bool
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function isContestsOrgAllowed($resource, $privilege): bool {
        $contest = $this->getContest();
        if (!$contest) {
            return false;
        }
        return $this->getContestAuthorizator()->isAllowed($resource, $privilege, $contest);
    }

    /**
     * @return array
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function getNavBarVariant(): array {
        return ['event event-type-' . $this->getEvent()->event_type_id, ($this->getEvent()->event_type_id == 1) ? 'bg-fyziklani navbar-dark' : 'bg-light navbar-light'];
    }

    /**
     * @return array
     */
    protected function getNavRoots(): array {
        return ['event.dashboard.default'];
    }

    /**
     * @return \FKSDB\ORM\Models\ModelContest
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected final function getContest(): ModelContest {
        return $this->getEvent()->getContest();
    }

    /**
     * @param string $name
     * @return \Nette\ComponentModel\IComponent|null
     * @throws \Exception
     */
    public function createComponent($name) {
        $parts = \explode('__', $name);
        if (\count($parts) === 3) {
            list($prefix, $tableName, $fieldName) = $parts;
            if ($prefix === 'valuePrinter') {
                return $this->tableReflectionFactory->createDetailComponent($tableName, $fieldName, 2048);
            }
        }

        return parent::createComponent($name);
    }

}
