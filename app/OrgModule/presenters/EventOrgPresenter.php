<?php

namespace OrgModule;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Grids\EventOrgsGrid;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\ORM\Services\ServiceEventOrg;
use Nette\Application\UI\Form;
use Persons\ExtendedPersonHandler;

/**
 * Class EventOrgPresenter
 * @package OrgModule
 */
class EventOrgPresenter extends ExtendedPersonPresenter {

    protected $modelResourceId = 'eventOrg';
    protected $fieldsDefinition = 'adminEventOrg';

    /**
     * @var ServiceEventOrg
     */
    private $serviceEventOrg;

    /**
     * @var \FKSDB\ORM\Services\ServiceEvent
     */
    private $serviceEvent;

    /**
     * @var \FKSDB\ORM\Models\ModelEvent
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
     * @param \FKSDB\ORM\Services\ServiceEvent $serviceEvent
     */
    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    public function titleEdit() {
        $model = $this->getModel();
        $this->setTitle(sprintf(_('Úprava organizátora %s akce %s'), $model->getPerson()->getFullname(), $model->getEvent()->name));
        $this->setIcon('fa fa-user');
    }

    public function titleCreate() {
        $this->setTitle(sprintf(_('Založit organizátora akce %s'), $this->getEvent()->name));
        $this->setIcon('fa fa-user-plus');
    }

    public function titleList() {
        $this->setTitle(sprintf(_('Organizátoři akce %s'), $this->getEvent()->name));
        $this->setIcon('fa fa-users');
    }

    /**
     * @param $id
     * @throws \Nette\Application\AbortException
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
     * @throws \Nette\Application\AbortException
     */
    public function actionDelete($id) {
        $success = $this->serviceEventOrg->getTable()->where('e_org_id', $id)->delete();
        if ($success) {
            $this->flashMessage(_('Organizátor akce smazán.'), self::FLASH_SUCCESS);
        } else {
            $this->flashMessage(_('Nepodařilo se smazat organizátora akce.'), self::FLASH_ERROR);
        }
        $this->redirect('list');
    }

    /**
     * @param \FKSDB\ORM\IModel|null $model
     * @param Form $form
     */
    protected function setDefaults(IModel $model = null, Form $form) {
        parent::setDefaults($model, $form);
        //$form[ExtendedPersonHandler::CONT_MODEL]->setDefaults([]);
    }

    /**
     * @param $name
     * @return EventOrgsGrid
     */
    protected function createComponentGrid($name): EventOrgsGrid {
        return new EventOrgsGrid($this->getEvent(), $this->serviceEventOrg);
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
     * @return \FKSDB\ORM\Models\ModelEvent
     */
    private function getEvent(): ModelEvent {
        if (!$this->modelEvent) {
            $this->modelEvent = ModelEvent::createFromTableRow($this->serviceEvent->findByPrimary($this->eventId));
        }
        return $this->modelEvent;
    }

}
