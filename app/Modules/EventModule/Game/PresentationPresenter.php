<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\Game\ResultsAndStatistics\ResultsAndStatisticsComponent;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use Fykosak\Utils\UI\PageTitle;

final class PresentationPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Results presentation'), 'fas fa-chalkboard');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): bool
    {
        return $this->eventAuthorizator->isAllowed('game.presentation', 'default', $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentPresentation(): ResultsAndStatisticsComponent
    {
        return new ResultsAndStatisticsComponent(
            $this->getContext(),
            $this->getEvent(),
            $this->getEvent()->event_type_id === 1 ? 'fyziklani.results.presentation' : 'ctyrboj.results.presentation'
        );
    }
}
