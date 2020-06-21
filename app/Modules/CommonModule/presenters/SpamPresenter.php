<?php

namespace FKSDB\Modules\CommonModule;

use FKSDB\Components\Grids\EmailsGrid;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Services\ServiceEmailMessage;
use FKSDB\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\Security\IResource;

/**
 * Class SpamPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SpamPresenter extends BasePresenter {
    use EntityPresenterTrait;

    /**
     * @var ServiceEmailMessage
     */
    private $serviceEmailMessage;

    /**
     * @param ServiceEmailMessage $serviceEmailMessage
     * @return void
     */
    public function injectServiceEmailMessage(ServiceEmailMessage $serviceEmailMessage) {
        $this->serviceEmailMessage = $serviceEmailMessage;
    }

    public function titleDetail() {
        $this->setPageTitle(new PageTitle(sprintf(_('Detail of email #%s'), $this->getEntity()->getPrimary()), 'fa fa-envelope'));
    }

    public function getTitleList(): PageTitle {
        return new PageTitle(_('List of emails'), 'fa fa-envelope');
    }

    public function authorizedDetail() {
        $authorized = true;
        foreach ($this->getServiceContest()->getTable() as $contest) {
            $authorized = $authorized && $this->contestAuthorizator->isAllowed($this->getORMService()->getModelClassName()::RESOURCE_ID, 'detail', $contest);
        }
        $this->setAuthorized($authorized);
    }

    /**
     * @return void
     */
    public function renderDetail() {
        $this->template->model = $this->getEntity();
    }

    protected function getORMService(): ServiceEmailMessage {
        return $this->serviceEmailMessage;
    }

    protected function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }

    protected function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    protected function createComponentGrid(): EmailsGrid {
        return new EmailsGrid($this->getContext());
    }

    /**
     * @param IResource|string $resource
     * @param string $privilege
     * @return bool
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isAnyContestAuthorized($resource, $privilege);
    }
}
