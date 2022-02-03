<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Fyziklani;

use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsAndStatisticsComponent;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use Fykosak\Utils\UI\PageTitle;

class PresentationPresenter extends BasePresenter
{
    public function titlePresentation(): PageTitle
    {
        return new PageTitle(null, _('Results presentation'), 'fas fa-chalkboard');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedPresentation(): void
    {
        $this->setAuthorized($this->isAllowed('fyziklani.presentation', 'default'));
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentPresentation(): ResultsAndStatisticsComponent
    {
        return new ResultsAndStatisticsComponent(
            $this->getContext(),
            $this->getEvent(),
            'fyziklani.results.presentation'
        );
    }

    protected function beforeRender(): void
    {
        $this->getPageStyleContainer()->setWidePage();
        parent::beforeRender();
    }
}
