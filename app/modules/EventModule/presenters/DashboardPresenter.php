<?php

namespace EventModule;

class DashboardPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(\sprintf(_('Event %s'), $this->getEvent()->name));
        $this->setIcon('fa fa-dashboard');
    }

    public function authorizedDefault() {
        $this->setAuthorized($this->eventIsAllowed('event.dashboard', 'default'));
    }

    public function renderDefault() {
        $this->template->event = $this->getEvent();
        $this->template->webUrl = $this->getWebUrl();
    }

    private function getWebUrl() {
        switch ($this->getEvent()->event_type_id) {
            case 1:
                // FOF
                return 'http://fyziklani.cz/';
            case 2:
                // DSEF
                return \sprintf('https://fykos.cz/rocnik%02d/dsef/', $this->getEvent()->year);
            case 3:
                // VAF
                return \sprintf('https://fykos.cz/rocnik%02d/vaf/', $this->getEvent()->year);
            case 4:
                // sous-jaro
                return \sprintf('https://fykos.cz/rocnik%02d/sous-jaro/', $this->getEvent()->year);
            case 5:
                // sous-podzim
                return \sprintf('https://fykos.cz/rocnik%02d/sous-podzim/', $this->getEvent()->year);
            case 6:
                // cern
                return \sprintf('https://fykos.cz/rocnik%02d/cern/', $this->getEvent()->year);
            case 7:
                // TSAF
                return \sprintf('https://fykos.cz/rocnik%02d/tsaf/', $this->getEvent()->year);
            case 8:
                // MFnáboj
                return '#'; // FIXME
            case 9:
                // FOL
                return \sprintf('https://online.fyziklani.cz', $this->getEvent()->year);
            // 1 Fyziklání online
            case 10:
                // Tábor výfuku
                return \sprintf('http://vyfuk.mff.cuni.cz/akce/tabor/tabor%d', $this->getEvent()->begin->format('Y'));
            case 11:
                // setkani jaro
                return \sprintf('http://vyfuk.mff.cuni.cz/akce/setkani/jaro%d', $this->getEvent()->begin->format('Y'));
            case 12:
                // setkani podzim
                return \sprintf('http://vyfuk.mff.cuni.cz/akce/setkani/podzim%d', $this->getEvent()->begin->format('Y'));
            case 13:
                // Náboj Junior
                return \sprintf('#'); // FIXME
            case 14:
                //DSEF 2
                return \sprintf('https://fykos.cz/rocnik%02d/dsef2/', $this->getEvent()->year);
            default:
                return '#';
        }
    }
}
