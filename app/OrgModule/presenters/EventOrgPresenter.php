<?php

namespace OrgModule;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Grids\EventOrgsGrid;
use ModelEvent;
use Nette\Application\UI\Form;
use Persons\ExtendedPersonHandler;
use ServiceEvent;
use ServiceEventOrg;

class EventOrgPresenter extends ExtendedPersonPresenter {

    protected $modelResourceId = 'eventOrg';
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

    public function injectServiceEventOrg(ServiceEventOrg $serviceEventOrg) {
        $this->serviceEventOrg = $serviceEventOrg;
    }
    
    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    public function titleEdit($id) {
        $model = $this->getModel();
        $this->setTitle(sprintf(_('Úprava organizátora %s akce %s'), $model->getPerson()->getFullname(), (string) $model->getEvent()));
    }

    public function renderEdit($id) {
        parent::renderEdit($id);

        $eventOrg = $this->getModel();

        if ($eventOrg->event_id != $this->eventId) {
            $this->flashMessage(_('Editace organizátora akce mimo zvolenou akci.'), self::FLASH_ERROR);
            $this->redirect('list');
        }
    }

    public function titleCreate() {
        $this->setTitle(sprintf(_('Založit organizátora akce %s'), (string) $this->getEvent()));
    }

    public function titleList() {
        $this->setTitle(sprintf(_('Organizátoři akce %s'), (string) $this->getEvent()));
    }
    
    public function actionDelete($id) {
        $success = $this->serviceEventOrg->getTable()->where('e_org_id', $id)->delete();
        if ($success) {
            $this->flashMessage(_('Organizátor akce smazán.'), self::FLASH_SUCCESS);
        } else {
            $this->flashMessage(_('Nepodařilo se smazat organizátora akce.'), self::FLASH_ERROR);
        }
        $this->redirect('list');
    }

    protected function createComponentGrid($name) {
        return new EventOrgsGrid($this->eventId, $this->serviceEventOrg);
    }

    protected function appendExtendedContainer(Form $form) {
        $container = new ModelContainer();
        $container->setCurrentGroup(null);
        $container->addText('note', _('Poznámka'));
        $container->addHidden('event_id', $this->eventId);
        $form->addComponent($container, ExtendedPersonHandler::CONT_MODEL);
    }

    protected function getORMService() {
        return $this->serviceEventOrg;
    }

    public function messageCreate() {
        return _('Organizátor akce %s založen.');
    }

    public function messageEdit() {
        return _('Organizátor akce %s upraven.');
    }

    public function messageError() {
        return _('Chyba při zakládání organizátora akce.');
    }
    
    public function messageExists() {
        return _('Organizátor akce již existuje.');
    }
    
    /**
     * 
     * @return ModelEvent
     */
    private function getEvent() {
        if(!$this->modelEvent) {
            $this->modelEvent = $this->serviceEvent->findByPrimary($this->eventId);
        }
        return $this->modelEvent;
    }

}