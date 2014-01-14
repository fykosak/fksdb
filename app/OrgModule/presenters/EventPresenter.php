<?php

namespace OrgModule;

use AbstractModelSingle;
use Events\Model\Grid\SingleEventSource;
use FKSDB\Components\Events\ApplicationsGrid;
use FKSDB\Components\Forms\Factories\EventFactory;
use FKSDB\Components\Grids\Events\EventsGrid;
use FKSDB\Components\Grids\Events\LayoutResolver;
use FormUtils;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use ModelException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Diagnostics\Debugger;
use Nette\NotImplementedException;
use ServiceEvent;
use SystemContainer;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class EventPresenter extends EntityPresenter {

    const CONT_EVENT = 'event';

    protected $modelResourceId = 'event';

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * @var EventFactory
     */
    private $eventFactory;

    /**
     *
     * @var LayoutResolver
     */
    private $layoutResolver;

    /**
     * @var SystemContainer
     */
    private $container;

    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    public function injectEventFactory(EventFactory $eventFactory) {
        $this->eventFactory = $eventFactory;
    }

    public function injectLayoutResolver(LayoutResolver $layoutResolver) {
        $this->layoutResolver = $layoutResolver;
    }

    public function injectContainer(Container $container) {
        $this->container = $container;
    }

    public function titleList() {
        $this->setTitle(_('Akce'));
    }

    public function titleCreate() {
        $this->setTitle(_('Přidat akci'));
    }

    public function titleEdit($id) {
        $model = $this->getModel();
        $this->setTitle(sprintf(_('Úprava akce %s'), $model->name));
    }

    public function titleApplications($id) {
        $model = $this->getModel();
        $this->setTitle(sprintf(_('Přihlášky akce %s'), $model->name));
    }

    public function actionDelete($id) {
        // There's no use case for this. (Errors must be deleted manually via SQL.)
        throw new NotImplementedException();
    }

    public function renderApplications($id) {
        $this->template->event = $this->getModel();
    }

    protected function createComponentCreateComponent($name) {
        $form = $this->createForm();

        $form->addSubmit('send', _('Přidat'));
        $form->onSuccess[] = array($this, 'handleCreateFormSuccess');

        return $form;
    }

    protected function createComponentEditComponent($name) {
        $form = $this->createForm();

        $form->addSubmit('send', _('Uložit'));
        $form->onSuccess[] = array($this, 'handleEditFormSuccess');

        return $form;
    }

    protected function createComponentGrid($name) {
        $grid = new EventsGrid($this->serviceEvent);

        return $grid;
    }

    protected function createComponentApplicationsGrid($name) {
        $source = new SingleEventSource($this->getModel(), $this->container);

        $grid = new ApplicationsGrid($this->container, $source);
        $template = $this->layoutResolver->getTemplate($this->getModel());
        $grid->setTemplate($template);

        return $grid;
    }

    private function createForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());

        $eventContainer = $this->eventFactory->createEvent($this->getSelectedContest());
        $form->addComponent($eventContainer, self::CONT_EVENT);

        return $form;
    }

    protected function setDefaults(AbstractModelSingle $model, Form $form) {
        $defaults = array(
            self::CONT_EVENT => $model->toArray(),
        );
        $form->setDefaults($defaults);
    }

    protected function createModel($id) {
        return $this->serviceEvent->findByPrimary($id);
    }

    /**
     * @internal
     * @param Form $form
     */
    public function handleCreateFormSuccess(Form $form) {
        $connection = $this->serviceEvent->getConnection();
        $values = $form->getValues();


        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }

            /*
             * Event
             */
            $data = FormUtils::emptyStrToNull($values[self::CONT_EVENT]);
            $model = $this->serviceEvent->createNew($data);
            $model->year = $this->getSelectedYear();
            // TODO ACL check
            $this->serviceEvent->save($model);

            /*
             * Finalize
             */
            if (!$connection->commit()) {
                throw new ModelException();
            }

            $this->flashMessage(sprintf(_('Akce %s přidána.'), $model->name), self::FLASH_SUCCESS);
            $this->backlinkRedirect();
            $this->redirect('list'); // if there's no backlink
        } catch (ModelException $e) {
            $connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage(_('Chyba přidání akce.'), self::FLASH_ERROR);
        }
    }

    /**
     * @internal
     * @param Form $form
     */
    public function handleEditFormSuccess(Form $form) {
        $connection = $this->serviceEvent->getConnection();
        $values = $form->getValues();
        $model = $this->getModel();

        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }

            /*
             * Event
             */
            $data = FormUtils::emptyStrToNull($values[self::CONT_EVENT]);
            $this->serviceEvent->updateModel($model, $data);
            $this->serviceEvent->save($model);

            /*
             * Finalize
             */
            if (!$connection->commit()) {
                throw new ModelException();
            }

            $this->flashMessage(sprintf(_('Akce %s upravena.'), $model->name), self::FLASH_SUCCESS);
            $this->backlinkRedirect();
            $this->redirect('list'); // if there's no backlink
        } catch (ModelException $e) {
            $connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage(_('Chyba při úpravě akce.'), self::FLASH_ERROR);
        }
    }

}
