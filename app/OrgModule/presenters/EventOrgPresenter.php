<?php

namespace OrgModule;

use FKSDB\Components\Grids\EventOrgsGrid;
use Nette\Application\UI\Form;
use ORM\IModel;
use Persons\ExtendedPersonHandler;
use ServiceEventOrg;
use FKSDB\Components\Forms\Containers\ModelContainer;

class EventOrgPresenter extends ExtendedPersonPresenter {

    protected $modelResourceId = 'eventOrg';
    protected $fieldsDefinition = 'adminEventOrg';

    /**
     * @var ServiceEventOrg
     */
    private $serviceEventOrg;
    
    /**
     *
     * @persistent
     */
    public $eventId;

    public function injectServiceOrg(ServiceEventOrg $serviceEventOrg) {
        $this->serviceEventOrg = $serviceEventOrg;
    }

    public function titleEdit($id) {
        $this->setTitle(sprintf(_('Úprava organizátora %s'), $this->getModel()->getPerson()->getFullname()));
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
        $this->setTitle(_('Založit organizátora akce'));
    }

    public function titleList() {
        $this->setTitle(_('Organizátoři akce'));
    }
    
    public function actionDelete($id) {
        $success = $this->serviceEventOrg->getTable()->where('e_org_id', $id)->delete();
        if($success){
            $this->flashMessage(_('Organizátor akce smazán.'), self::FLASH_SUCCESS);
        }
        else{
            $this->flashMessage(_('Nepodařilo se smazat organizátora akce.'), self::FLASH_ERROR);
        }
        $this->redirect('list');
    }

    protected function setDefaults(IModel $model = null, Form $form) {
        parent::setDefaults($model, $form);
        //$form[ExtendedPersonHandler::CONT_MODEL]->setDefaults(array());
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

}