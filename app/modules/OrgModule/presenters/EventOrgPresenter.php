<?php

namespace OrgModule;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Grids\BaseGrid;
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
 * *
 * @method ModelEventOrg getModel()
 */
class EventOrgPresenter extends ExtendedPersonPresenter {

    protected string $fieldsDefinition = 'adminEventOrg';

    private ServiceEventOrg $serviceEventOrg;

    private ServiceEvent $serviceEvent;

    private ModelEvent $modelEvent;

    /**
     * @persistent
     */
    public $eventId;

    public function injectServiceEventOrg(ServiceEventOrg $serviceEventOrg): void {
        $this->serviceEventOrg = $serviceEventOrg;
    }

    public function injectServiceEvent(ServiceEvent $serviceEvent): void {
        $this->serviceEvent = $serviceEvent;
    }

    public function titleEdit(): void {
        $model = $this->getModel();
        $this->setTitle(sprintf(_('Úprava organizátora %s akce %s'), $model->getPerson()->getFullName(), $model->getEvent()->name), 'fa fa-user');
    }

    public function titleCreate(): void {
        $this->setTitle(sprintf(_('Založit organizátora akce %s'), $this->getEvent()->name), 'fa fa-user-plus');
    }

    /**
     * @param $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderEdit($id): void {
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
    public function actionDelete(int $id): void {
        $success = $this->serviceEventOrg->getTable()->wherePrimary($id)->delete();
        if ($success) {
            $this->flashMessage(_('Organizátor akce smazán.'), self::FLASH_SUCCESS);
        } else {
            $this->flashMessage(_('Nepodařilo se smazat organizátora akce.'), self::FLASH_ERROR);
        }
        $this->redirect('list');
    }

    protected function appendExtendedContainer(Form $form): void {
        $container = new ModelContainer();
        $container->setCurrentGroup(null);
        $container->addText('note', _('Poznámka'));
        $container->addHidden('event_id', $this->eventId);
        $form->addComponent($container, ExtendedPersonHandler::CONT_MODEL);
    }

    protected function getORMService(): ServiceEventOrg {
        return $this->serviceEventOrg;
    }

    public function messageCreate(): string {
        return _('Organizátor akce %s založen.');
    }

    public function messageEdit(): string {
        return _('Organizátor akce %s upraven.');
    }

    public function messageError(): string {
        return _('Chyba při zakládání organizátora akce.');
    }

    public function messageExists(): string {
        return _('Organizátor akce již existuje.');
    }

    private function getEvent(): ModelEvent {
        if (!isset($this->modelEvent)) {
            $this->modelEvent = $this->serviceEvent->findByPrimary($this->eventId);
        }
        return $this->modelEvent;
    }

    /**
     * @return mixed|void
     * @throws NotImplementedException
     */
    protected function createComponentGrid(): BaseGrid {
        throw new NotImplementedException();
    }

    protected function getModelResource(): string {
        return 'eventOrg';
    }
}
