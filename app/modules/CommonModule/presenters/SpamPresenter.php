<?php

namespace CommonModule;

use FKSDB\Components\Grids\EmailsGrid;
use FKSDB\ORM\Services\ServiceEmailMessage;

/**
 * Class MailSenderPresenter
 * @package OrgModule
 */
class SpamPresenter extends BasePresenter {
    /**
     * @var ServiceEmailMessage
     */
    private $serviceEmailMessage;

    /**
     * @param ServiceEmailMessage $serviceEmailMessage
     */
    public function injectServiceEmailMessage(ServiceEmailMessage $serviceEmailMessage) {
        $this->serviceEmailMessage = $serviceEmailMessage;
    }

    public function titleList() {
        $this->setTitle(_('List of emails'));
        $this->setIcon('fa fa-envelope');
    }

    public function authorizedList() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowedForAnyContest('spam', 'list'));
    }

    /**
     * @return EmailsGrid
     */
    protected function createComponentGrid(): EmailsGrid {
        return new EmailsGrid($this->serviceEmailMessage, $this->getTableReflectionFactory());
    }
}
