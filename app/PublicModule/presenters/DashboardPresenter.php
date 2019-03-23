<?php

namespace PublicModule;

use AuthenticationPresenter;
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
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\ForbiddenRequestException
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
        $this->setTitle(_('Pultík'));
        $this->setIcon('fa fa-dashboard');
    }

    /**
     * @throws \Nette\Application\BadRequestException
     */
    public function renderDefault() {
        foreach ($this->news->getNews($this->getSelectedContest(), $this->getSelectedLanguage())
	  as $new) {
            $this->flashMessage($new);
        }
    }

}
