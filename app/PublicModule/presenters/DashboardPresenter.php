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

    public function injectNews(News $news) {
        $this->news = $news;
    }

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

    public function renderDefault() {
        foreach ($this->news->getNews($this->getSelectedContest(), $this->getSelectedLanguage())
                 as $new) {
            $this->flashMessage($new);
        }
    }

}
