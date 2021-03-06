<?php

namespace FKSDB\Modules\PublicModule;

use FKSDB\Models\Localization\UnsupportedLanguageException;
use FKSDB\Models\News;
use FKSDB\Models\UI\PageTitle;

/**
 * Just proof of concept.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DashboardPresenter extends BasePresenter {

    private News $news;

    final public function injectNews(News $news): void {
        $this->news = $news;
    }

    public function authorizedDefault(): void {
        $login = $this->getUser()->getIdentity();
        $this->setAuthorized((bool)$login);
    }

    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('Dashboard'), 'fas fa-chalkboard'));
    }

    /**
     * @throws UnsupportedLanguageException
     */
    final public function renderDefault(): void {
        foreach ($this->news->getNews($this->getSelectedContest(), $this->getLang()) as $new) {
            $this->flashMessage($new);
        }
    }
}
