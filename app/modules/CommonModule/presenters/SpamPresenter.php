<?php

namespace CommonModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\EmailsGrid;
use FKSDB\EntityTrait;
use FKSDB\NotImplementedException;
use FKSDB\ORM\Models\ModelEmailMessage;
use FKSDB\ORM\Services\ServiceEmailMessage;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

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
        $this->setTitle(sprintf(_('Detail of email #%s'), $this->loadEntity($id)->getPrimary()));
        $this->setIcon('fa fa-envelope');
    }

    public function titleList() {
        $this->setTitle(_('List of emails'));
        $this->setIcon('fa fa-envelope');
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function renderDetail(int $id) {
        $this->template->model = $this->loadEntity($id);
    }

    /**
     * @return EmailsGrid
     */
    protected function createComponentGrid(): EmailsGrid {
        return new EmailsGrid($this->serviceEmailMessage, $this->getTableReflectionFactory());
    }

    /**
     * @inheritDoc
     */
    protected function getORMService() {
        return $this->serviceEmailMessage;
    }

    /**
     * @inheritDoc
     */
    protected function getModelResource(): string {
        return ModelEmailMessage::RESOURCE_ID;
    }

    /**
     * @inheritDoc
     */
    protected function getCreateForm(): FormControl {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    protected function getEditForm(): FormControl {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    protected function handleCreateFormSuccess(Form $form) {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    protected function handleEditFormSuccess(Form $form) {
        throw new NotImplementedException();
    }
}
