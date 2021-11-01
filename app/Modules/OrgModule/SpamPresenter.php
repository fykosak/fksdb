<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Grids\EmailsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Services\ServiceEmailMessage;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

class SpamPresenter extends BasePresenter
{
    use EntityPresenterTrait;

    private ServiceEmailMessage $serviceEmailMessage;

    final public function injectServiceEmailMessage(ServiceEmailMessage $serviceEmailMessage): void
    {
        $this->serviceEmailMessage = $serviceEmailMessage;
    }

    /**
     * @throws ModelNotFoundException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            sprintf(_('Detail of email #%s'), $this->getEntity()->getPrimary()),
            'fas fa-envelope-open'
        );
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(_('List of emails'), 'fas fa-mail-bulk');
    }

    public function authorizedDetail(): void
    {
        $authorized = true;
        foreach ($this->serviceContest->getTable() as $contest) {
            $authorized = $authorized
                && $this->contestAuthorizator->isAllowed(
                    $this->getORMService()->getModelClassName()::RESOURCE_ID,
                    'detail',
                    $contest
                );
        }
        $this->setAuthorized($authorized);
    }

    protected function getORMService(): ServiceEmailMessage
    {
        return $this->serviceEmailMessage;
    }

    /**
     * @throws ModelNotFoundException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    protected function createComponentEditForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentCreateForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentGrid(): EmailsGrid
    {
        return new EmailsGrid($this->getContext());
    }

    /**
     * @param Resource|string|null $resource
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAnyContestAuthorized($resource, $privilege);
    }
}
