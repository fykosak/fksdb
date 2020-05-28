<?php

namespace CommonModule;

use FKSDB\Components\Grids\EmailsGrid;
use FKSDB\EntityTrait;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Services\ServiceEmailMessage;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Security\IResource;

/**
 * Class MailSenderPresenter
 * *
 */
class SpamPresenter extends BasePresenter {
    use EntityTrait;

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
     * @param IResource|string $resource
     * @param string $privilege
     * @return bool
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isAnyContestAuthorized($resource, $privilege);
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function renderDetail(int $id) {
        $this->template->model = $this->loadEntity($id);
    }

    /**
     * @return ServiceEmailMessage
     */
    protected function getORMService() {
        return $this->serviceEmailMessage;
    }

    public function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }

    public function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    protected function createComponentGrid(): EmailsGrid {
        return new EmailsGrid($this->getContext());
    }
}
