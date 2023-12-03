<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Components\DataTest\TestsList;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use Fykosak\Utils\UI\PageTitle;

final class ReportPresenter extends BasePresenter
{
    private DataTestFactory $dataTestFactory;

    public function inject(DataTestFactory $dataTestFactory): void
    {
        $this->dataTestFactory = $dataTestFactory;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Report'), 'fas fa-calendar-alt');
    }

    public function authorizedDefault(): bool
    {
        return true;
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    final public function renderDefault(): void
    {
        set_time_limit(-1);
        $this->template->contestYear = $this->getSelectedContestYear();
    }

    /**
     * @phpstan-return TestsList<ContestYearModel>
     */
    protected function createComponentTests(): TestsList
    {
        return new TestsList($this->getContext(), $this->dataTestFactory->getContestYearTests());
    }
}
