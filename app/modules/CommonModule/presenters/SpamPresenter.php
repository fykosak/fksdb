<?php

namespace CommonModule;

use FKSDB\Components\Grids\EmailsGrid;
use FKSDB\EntityTrait;
use FKSDB\NotImplementedException;
use FKSDB\ORM\Models\ModelEmailMessage;
use FKSDB\ORM\Services\ServiceEmailMessage;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Security\IResource;

/**
 * Class MailSenderPresenter
 * @package OrgModule
 */
class SpamPresenter extends BasePresenter {
    use EntityTrait;
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

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function titleDetail(int $id) {
        $this->setTitle(sprintf(_('Detail of email #%s'), $this->loadEntity($id)->getPrimary()), 'fa fa-envelope');
    }

    public function titleList() {
        $this->setTitle(_('List of emails'), 'fa fa-envelope');
    }

    /**
     * @inheritDoc
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isAnyContestAllowed($resource, $privilege);
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function renderDetail(int $id) {
        $this->template->model = $this->loadEntity($id);
    }

    /**
     * @inheritDoc
     */
    protected function getORMService() {
        return $this->serviceEmailMessage;
    }

    /** @inheritDoc */
    public function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }

    /** @inheritDoc */
    public function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @return EmailsGrid
     */
    protected function createComponentGrid(): EmailsGrid {
        return new EmailsGrid($this->getContext());
    }

}
