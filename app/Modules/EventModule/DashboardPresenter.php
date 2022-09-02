<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use Fykosak\Utils\UI\PageTitle;

class DashboardPresenter extends BasePresenter
{

    /**
     * @throws EventNotFoundException
     */
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, \sprintf(_('Event %s'), $this->getEvent()->name), 'fa fa-calendar-alt');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): void
    {
        $this->setAuthorized($this->isAllowed('event.dashboard', 'default'));
    }

    /**
     * @throws EventNotFoundException
     */
    final public function renderDefault(): void
    {
        $this->getTemplate()->event = $this->getEvent();
        $this->getTemplate()->webUrl = $this->getWebUrl();
    }

    /**
     * @throws EventNotFoundException
     */
    private function getWebUrl(): string
    {
        switch ($this->getEvent()->event_type_id) {
            case 1:
                // FOF
                return 'https://fyziklani.cz/';
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
                return 'https://online.fyziklani.cz';
            // 1 Fyziklání online
            case 10:
                // Tábor výfuku
                return \sprintf('https://vyfuk.mff.cuni.cz/akce/tabor/tabor%d', $this->getEvent()->begin->format('Y'));
            case 11:
                // setkani jaro
                return \sprintf('https://vyfuk.mff.cuni.cz/akce/setkani/jaro%d', $this->getEvent()->begin->format('Y'));
            case 12:
                // setkani podzim
                return \sprintf(
                    'https://vyfuk.mff.cuni.cz/akce/setkani/podzim%d',
                    $this->getEvent()->begin->format('Y')
                );
            case 13:
                // Náboj Junior
                return '#'; // FIXME
            case 14:
                //DSEF 2
                return \sprintf('https://fykos.cz/rocnik%02d/dsef2/', $this->getEvent()->year);
            default:
                return '#';
        }
    }
}
