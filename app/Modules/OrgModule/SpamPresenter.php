<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Grids\EmailsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Services\ServiceEmailMessage;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\InvalidStateException;
use Nette\Security\IResource;

/**
 * Class SpamPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SpamPresenter extends BasePresenter {
    use EntityPresenterTrait;

    private ServiceEmailMessage $serviceEmailMessage;

    final public function injectServiceEmailMessage(ServiceEmailMessage $serviceEmailMessage): void {
        $this->serviceEmailMessage = $serviceEmailMessage;
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws InvalidStateException
     */
    public function titleDetail(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Detail of email #%s'), $this->getEntity()->getPrimary()), 'fa fa-envelope'));
    }

    public function getTitleList(): PageTitle {
        return new PageTitle(_('List of emails'), 'fa fa-envelope');
    }

    public function authorizedDetail(): void {
        $authorized = true;
        foreach ($this->serviceContest->getTable() as $contest) {
            $authorized = $authorized && $this->contestAuthorizator->isAllowed($this->getORMService()->getModelClassName()::RESOURCE_ID, 'detail', $contest);
        }
        $this->setAuthorized($authorized);
    }

    /**
     * @return void
     * @throws ModelNotFoundException
     */
    public function renderDetail(): void {
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
     * @param string|null $privilege
     * @return bool
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return $this->isAnyContestAuthorized($resource, $privilege);
    }
}
