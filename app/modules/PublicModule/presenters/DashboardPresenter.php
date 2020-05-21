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
    /**
     * @var News
     */
    private $news;

    /**
     * @param News $news
     */
    public function injectNews(News $news) {
        $this->news = $news;
    }

    /**
     * @throws AbortException
     * @throws ForbiddenRequestException
     */
    protected function unauthorizedAccess() {
        if ($this->getParam(AuthenticationPresenter::PARAM_DISPATCH)) {
            parent::unauthorizedAccess();
        } else {
            $this->redirect(':Authentication:login'); // ask for a central dispatch
        }
    }

    public function authorizedDefault() {
        $login = $this->getUser()->getIdentity();
        $access = (bool)$login;
        $this->setAuthorized($access);
    }

    public function titleDefault() {
        $this->setTitle(_('Pultík'),'fa fa-dashboard');
    }

    /**
     * @throws BadRequestException
     */
    public function renderDefault() {
        foreach ($this->news->getNews($this->getSelectedContest(), $this->getLang())
	  as $new) {
            $this->flashMessage($new);
        }
    }

}
