<?php
/**
 * Created by PhpStorm.
 * User: miso
 * Date: 28.12.2016
 * Time: 14:57
 */

namespace FyziklaniModule;


class DefaultPresenter extends BasePresenter {
    public function titleDefault() {
        $this->setTitle(_('Fykosí Fyzikláni'));
    }

    public function authorizedDefault() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowedEvent('fyziklani', 'default', $this->getCurrentEvent(), $this->database));
    }

}