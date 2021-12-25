<?php

declare(strict_types=1);

namespace FKSDB\Modules\PublicModule;

use FKSDB\Models\News;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Fykosak\Utils\UI\PageTitle;

/**
 * Just proof of concept.
 */
class DashboardPresenter extends BasePresenter
{

    private News $news;

    final public function injectNews(News $news): void
    {
        $this->news = $news;
    }

    public function authorizedDefault(): void
    {
        $login = $this->getUser()->getIdentity();
        $this->setAuthorized((bool)$login);
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(_('Dashboard'), 'fas fa-chalkboard');
    }

    /**
     * @throws UnsupportedLanguageException
     */
    final public function renderDefault(): void
    {
        foreach ($this->news->getNews($this->getSelectedContest(), $this->getLang()) as $new) {
            $this->flashMessage($new);
        }
    }
}
