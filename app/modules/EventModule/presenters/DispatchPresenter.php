<?php


namespace EventModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\LanguageChooser;
use FKSDB\Components\Controls\Stalking\Helpers\ContestBadge;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelPerson;
use Nette\DI\Container;
use ServiceEvent;

class DispatchPresenter extends AuthenticatedPresenter {

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

    public function createComponentContestBadge() {
        return new ContestBadge();
    }

    public function titleDefault() {
        $this->setTitle(_('Výber eventu'));
        $this->setIcon(_('fa fa-calendar'));
    }

    public function renderDefault() {
        /**
         * @var $person ModelPerson
         */
        $person = $this->user->getIdentity()->getPerson();
        $events = [];
        foreach ($this->serviceEvent->getTable() as $row) {
            $modelEvent = ModelEvent::createFromTableRow($row);
            $isEventParticipant = $person->isEventParticipant($modelEvent->event_id);
            $isEventOrg = count($person->getEventOrg()->where('event_id', $modelEvent->event_id));
            $isOrg = \array_key_exists($modelEvent->getEventType()->contest_id, $person->getActiveOrgs($this->getYearCalculator()));
            if ($isEventParticipant || $isEventOrg || $isOrg) {
                $events[] = [
                    'model' => $modelEvent,
                    'permissions' => [
                        'isEventParticipant' => $isEventParticipant,
                        'isEventOrg' => $isEventOrg,
                        'isOrg' => $isOrg,
                    ],
                ];
            }
        }
        $this->template->events = $events;
    }

    /**
     */
    public function startup() {
        /**
         * @var $languageChooser LanguageChooser
         */
        $languageChooser = $this['languageChooser'];
        $languageChooser->syncRedirect();

        parent::startup();
    }

    public function getNavBarVariant() {
        return ['event', 'light'];
    }
}
