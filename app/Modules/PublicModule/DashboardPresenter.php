<?php

namespace FKSDB\Modules\PublicModule;


use Fykosak\Utils\Localization\UnsupportedLanguageException;

use FKSDB\Modules\CoreModule\AuthenticationPresenter;
use FKSDB\Models\News;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;

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

    /**
     * @throws AbortException
     * @throws ForbiddenRequestException
     */
    protected function unauthorizedAccess(): void {
        if ($this->getParameter(AuthenticationPresenter::PARAM_DISPATCH)) {
            parent::unauthorizedAccess();
        } else {
            $this->redirect(':Core:Authentication:login'); // ask for a central dispatch
        }
    }

    public function authorizedDefault(): void {
        $login = $this->getUser()->getIdentity();
        $this->setAuthorized((bool)$login);
    }

    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('Dashboard'), 'fa fa-dashboard'));
    }

    /**
     * @throws UnsupportedLanguageException
     */
    public function renderDefault(): void {
        foreach ($this->news->getNews($this->getSelectedContest(), $this->getLang()) as $new) {
            $this->flashMessage($new);
        }
    }
}
