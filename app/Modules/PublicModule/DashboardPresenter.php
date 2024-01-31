<?php

declare(strict_types=1);

namespace FKSDB\Modules\PublicModule;

use FKSDB\Models\News;
use FKSDB\Modules\Core\Language;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use Fykosak\Utils\UI\PageTitle;

final class DashboardPresenter extends BasePresenter
{
    private News $news;

    final public function injectNews(News $news): void
    {
        $this->news = $news;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Dashboard'), 'fas fa-chalkboard');
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    public function authorizedDefault(): bool
    {
        return $this->contestYearAuthorizator->isAllowed(
            $this->getSelectedContest(),
            'contestantDashboard',
            $this->getSelectedContestYear()
        );
    }

    /**
     * @throws NoContestAvailable
     */
    final public function renderDefault(): void
    {
        foreach ($this->news->getNews($this->getSelectedContest(), Language::from($this->translator->lang)) as $new) {
            $this->flashMessage($new);
        }
    }
}
