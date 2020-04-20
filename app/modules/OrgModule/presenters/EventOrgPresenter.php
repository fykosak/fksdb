<?php

namespace OrgModule;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventOrg;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\ORM\Services\ServiceEventOrg;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Persons\ExtendedPersonHandler;

/**
 * Class EventOrgPresenter
 * @package OrgModule
 * @method ModelEventOrg getModel()
 */
class EventOrgPresenter extends ExtendedPersonPresenter {

    protected $fieldsDefinition = 'adminEventOrg';

    /**
     * @var ServiceEventOrg
     */
    private $serviceEventOrg;

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * @var ModelEvent
     */
    private $modelEvent;

    /**
     * @persistent
     */
    public $eventId;

    /**
     * @param ServiceEventOrg $serviceEventOrg
     */
    public function injectServiceEventOrg(ServiceEventOrg $serviceEventOrg) {
        $this->serviceEventOrg = $serviceEventOrg;
    }

    /**
     * @param ServiceEvent $serviceEvent
     */
    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    public function titleEdit() {
        $model = $this->getModel();
        $this->setTitle(sprintf(_('Úprava organizátora %s akce %s'), $model->getPerson()->getFullName(), $model->getEvent()->name), 'fa fa-user');
    }

    public function titleCreate() {
        $this->setTitle(sprintf(_('Založit organizátora akce %s'), $this->getEvent()->name), 'fa fa-user-plus');
    }

    /**
     * @param $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderEdit($id) {
        parent::renderEdit($id);

        $eventOrg = $this->getModel();

        if ($eventOrg->event_id != $this->eventId) {
            $this->flashMessage(_('Editace organizátora akce mimo zvolenou akci.'), self::FLASH_ERROR);
            $this->redirect('list');
        }
    }

    /**
     * @param $id
     * @throws AbortException
     */
    public function actionDelete(int $id) {
        $success = $this->serviceEventOrg->getTable()->wherePrimary($id)->delete();
        if ($success) {
            $this->flashMessage(_('Organizátor akce smazán.'), self::FLASH_SUCCESS);
        } else {
            $this->flashMessage(_('Nepodařilo se smazat organizátora akce.'), self::FLASH_ERROR);
        }
        $this->redirect('list');
    }

    /**
     * @param Form $form
     * @return mixed|void
     */
    protected function appendExtendedContainer(Form $form) {
        $container = new ModelContainer();
        $container->setCurrentGroup(null);
        $container->addText('note', _('Poznámka'));
        $container->addHidden('event_id', $this->eventId);
        $form->addComponent($container, ExtendedPersonHandler::CONT_MODEL);
    }

    /**
     * @return ServiceEventOrg
     */
    protected function getORMService(): ServiceEventOrg {
        return $this->serviceEventOrg;
    }

    /**
     * @return string
     */
    public function messageCreate() {
        return _('Organizátor akce %s založen.');
    }

    /**
     * @return string
     */
    public function messageEdit() {
        return _('Organizátor akce %s upraven.');
    }

    /**
     * @return string
     */
    public function messageError() {
        return _('Chyba při zakládání organizátora akce.');
    }

    /**
     * @return string
     */
    public function messageExists() {
        return _('Organizátor akce již existuje.');
    }

    /**
     * @return ModelEvent
     */
    private function getEvent(): ModelEvent {
        if (!$this->modelEvent) {
            $this->modelEvent = $this->serviceEvent->findByPrimary($this->eventId);
        }
        return $this->modelEvent;
    }

    /**
     * @inheritDoc
     * @throws NotImplementedException
     */
    protected function createComponentGrid() {
        throw new NotImplementedException;
    }

    /**
     * @return string
     */
    protected function getModelResource(): string {
        return 'eventOrg';
    }

}
