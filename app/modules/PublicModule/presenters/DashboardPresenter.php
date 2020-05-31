<?php

namespace PublicModule;

use AuthenticationPresenter;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use News;

/**
 * Just proof of concept.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DashboardPresenter extends BasePresenter {

    private News $news;

    public function injectNews(News $news): void {
        $this->news = $news;
    }

    /**
     * @throws AbortException
     * @throws ForbiddenRequestException
     */
    protected function unauthorizedAccess(): void {
        if ($this->getParam(AuthenticationPresenter::PARAM_DISPATCH)) {
            parent::unauthorizedAccess();
        } else {
            $this->redirect(':Authentication:login'); // ask for a central dispatch
        }
    }

    public function authorizedDefault(): void {
        $login = $this->getUser()->getIdentity();
        $access = (bool)$login;
        $this->setAuthorized($access);
    }

    public function titleDefault(): void {
        $this->setTitle(_('Pultík'), 'fa fa-dashboard');
    }

    /**
     * @throws BadRequestException
     */
    public function renderDefault(): void {
        foreach ($this->news->getNews($this->getSelectedContest(), $this->getLang())
                 as $new) {
            $this->flashMessage($new);
        }
    }

}
