<?php

namespace OrgModule;

use Events\Model\ApplicationHandlerFactory;
use Events\Model\Grid\SingleEventSource;
use FKS\Components\Controls\FormControl;
use FKS\Components\Forms\Containers\ContainerWithOptions;
use FKS\Config\NeonScheme;
use FKS\Logging\MemoryLogger;
use FKSDB\Components\Events\ApplicationsGrid;
use FKSDB\Components\Events\ExpressionPrinter;
use FKSDB\Components\Events\GraphComponent;
use FKSDB\Components\Events\ImportComponent;
use FKSDB\Components\Forms\Factories\EventFactory;
use FKSDB\Components\Forms\Factories\ReferencedPersonFactory;
use FKSDB\Components\Grids\EventOrgsGrid;
use FKSDB\Components\Grids\Events\EventsGrid;
use FKSDB\Components\Grids\Events\LayoutResolver;
use FormUtils;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Logging\FlashDumpFactory;
use ModelException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Controls\BaseControl;
use Nette\NotImplementedException;
use Nette\Utils\Html;
use Nette\Utils\Neon;
use Nette\Utils\NeonException;
use ORM\IModel;
use Persons\ExtendedPersonHandler;
use Persons\SelfResolver;
use ServiceEvent;
use SystemContainer;
use Utils;
use FKS\Config\Expressions\Helpers;


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
     * @var \ServicePerson
     */
    private $servicePerson;

    /**
     *
     * @var LayoutResolver
     */
    private $layoutResolver;

    /**
     * @var SystemContainer
     */
    private $container;

    /**
     * @var ExpressionPrinter
     */
    private $expressionPrinter;

    /**
     * @var ApplicationHandlerFactory
     */
    private $handlerFactory;

    /**
     * @var FlashDumpFactory
     */
    private $flashDumpFactory;

    /**
     * @var ReferencedPersonFactory
     */
    private $referencedPersonFactory;
    /**
     * @var \ServiceEventOrg
     */
    private $serviceEventOrg;


    public function injectServicePerson(\ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }


    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    public function injectEventFactory(EventFactory $eventFactory) {
        $this->eventFactory = $eventFactory;
    }

    public function injectLayoutResolver(LayoutResolver $layoutResolver) {
        $this->layoutResolver = $layoutResolver;
    }

    public function injectReferencedPersonFactory(ReferencedPersonFactory $referencedPersonFactory) {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    public function injectContainer(Container $container) {
        $this->container = $container;
    }

    public function injectExpressionPrinter(ExpressionPrinter $expressionPrinter) {
        $this->expressionPrinter = $expressionPrinter;
    }

    public function injectHandlerFactory(ApplicationHandlerFactory $handlerFactory) {
        $this->handlerFactory = $handlerFactory;
    }

    public function injectFlashDumpFactory(FlashDumpFactory $flashDumpFactory) {
        $this->flashDumpFactory = $flashDumpFactory;
    }

    public function injectServiceEventOrg(\ServiceEventOrg $serviceEventOrg) {
        $this->serviceEventOrg = $serviceEventOrg;
    }

    public function authorizedModel($id) {
        $model = $this->getModel();
        if (!$model) {
            throw new BadRequestException('Neexistující model.', 404);
        }
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($model, 'edit', $this->getSelectedContest()));
    }

    public function actionModel($id) {

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

    public function titleModel($id) {
        $model = $this->getModel();
        $this->setTitle(sprintf(_('Model akce %s'), $model->name));
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
        $that = $this;
        $form->onSuccess[] = function (Form $form) use ($that) {
            $that->handleFormSuccess($form, true);
        };

        return $form;
    }

    protected function createComponentEditComponent($name) {
        $form = $this->createForm();

        $form->addSubmit('send', _('Uložit'));
        $that = $this;
        $form->onSuccess[] = function (Form $form) use ($that) {
            $that->handleFormSuccess($form, false);
        };

        return $form;
    }

    protected function createComponentGrid($name) {
        $grid = new EventsGrid($this->serviceEvent);

        return $grid;
    }

    protected function createComponentApplicationsGrid($name) {
        $source = new SingleEventSource($this->getModel(), $this->container);
        $source->order('created');

        $flashDump = $this->flashDumpFactory->createApplication();
        $grid = new ApplicationsGrid($this->container, $source, $this->handlerFactory, $flashDump);
        $template = $this->layoutResolver->getTableLayout($this->getModel());
        $grid->setTemplate($template);
        $grid->setSearchable(true);

        return $grid;
    }

    protected function createComponentApplicationsImport($name) {
        $source = new SingleEventSource($this->getModel(), $this->container);
        $logger = new MemoryLogger(); //TODO log to file?
        $machine = $this->container->createEventMachine($this->getModel());
        $handler = $this->handlerFactory->create($this->getModel(), $logger);

        $flashDump = $this->flashDumpFactory->createApplication();
        $component = new ImportComponent($machine, $source, $handler, $flashDump, $this->container);
        return $component;
    }

    protected function createComponentGraphComponent($name) {
        $event = $this->getModel();
        $machine = $this->container->createEventMachine($event);

        $component = new GraphComponent($machine->getPrimaryMachine(), $this->expressionPrinter);
        return $component;
    }

    private function createForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());

        $eventContainer = $this->eventFactory->createEvent($this->getSelectedContest());
        $form->addComponent($eventContainer, self::CONT_EVENT);

        if ($event = $this->getModel()) { // intentionally =
            $holder = $this->container->createEventHolder($event);
            $scheme = $holder->getPrimaryHolder()->getParamScheme();
            $paramControl = $eventContainer->getComponent('parameters');
            $paramControl->setOption('description', $this->createParamDescription($scheme));
            $paramControl->addRule(function (BaseControl $control) use ($scheme) {
                $parameters = $control->getValue();
                try {
                    if ($parameters) {
                        $parameters = Neon::decode($parameters);
                    } else {
                        $parameters = array();
                    }

                    NeonScheme::readSection($parameters, $scheme);
                    return true;
                } catch (NeonException $e) {
                    $control->addError($e->getMessage());
                    return false;
                }
            }, _('Parametry nesplňují Neon schéma'));
        }

        return $form;
    }

    private function createParamDescription($scheme) {
        $result = Html::el('ul');
        foreach ($scheme as $key => $meta) {
            $item = Html::el('li');
            $result->add($item);

            $item->add(Html::el(null)->setText($key));
            if (isset($meta['default'])) {
                $item->add(': ');
                $item->add(Html::el(null)->setText(Utils::getRepr($meta['default'])));
            }
        }

        return $result;
    }

    protected function setDefaults(IModel $model = null, Form $form) {
        if (!$model) {
            return;
        }
        $defaults = array(
            self::CONT_EVENT => $model->toArray(),
        );
        $form->setDefaults($defaults);
    }

    protected function loadModel($id) {
        return $this->serviceEvent->findByPrimary($id);
    }

    /**
     * @param Form $form
     */
    private function handleFormSuccess(Form $form, $isNew) {
        $connection = $this->serviceEvent->getConnection();
        $values = $form->getValues();
        if ($isNew) {
            $model = $this->serviceEvent->createNew();
            $model->year = $this->getSelectedYear();
        } else {
            $model = $this->getModel();
        }


        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }

            /*
             * Event
             */
            $data = FormUtils::emptyStrToNull($values[self::CONT_EVENT]);
            $this->serviceEvent->updateModel($model, $data);

            if (!$this->getContestAuthorizator()->isAllowed($model, $isNew ? 'create' : 'edit', $this->getSelectedContest())) {
                throw new ForbiddenRequestException();
            }

            $this->serviceEvent->save($model);

            /*
             * Finalize
             */
            if (!$connection->commit()) {
                throw new ModelException();
            }

            $this->flashMessage(sprintf(_('Akce %s uložena.'), $model->name), self::FLASH_SUCCESS);
            $this->backlinkRedirect();
            $this->redirect('list'); // if there's no backlink
        } catch (ModelException $e) {
            $connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage(_('Chyba přidání akce.'), self::FLASH_ERROR);
        } catch (ForbiddenRequestException $e) {
            $connection->rollBack();
            $this->flashMessage(_('Nedostatečné oprávnění.'), self::FLASH_ERROR);
        }
    }

    public function createComponentOrgsGrid($name) {
        return new EventOrgsGrid($this->getParam('id'),$this->serviceEventOrg);

    }

    public function createComponentOrgForm($name) {
        $control = new FormControl();
        $form = $control->getForm();
        $control->setGroupMode(FormControl::GROUP_CONTAINER);

        $container = new ContainerWithOptions();
        $form->addComponent($container, ExtendedPersonHandler::CONT_AGGR);

        $fieldsDefinition = Helpers::evalExpressionArray($this->globalParameters['eventOrg'], $this->container);
        $modifiabilityResolver = $visibilityResolver = new SelfResolver($this->getUser());

        $components = $this->referencedPersonFactory->createReferencedPerson($fieldsDefinition, null, ReferencedPersonFactory::SEARCH_ID, false, $modifiabilityResolver, $visibilityResolver);

        $container->addComponent($components[0], ExtendedPersonHandler::EL_PERSON);
        $container->addComponent($components[1], ExtendedPersonHandler::CONT_PERSON);


        //  $this->appendExtendedContainer($form);
        $form->addText('note', _('Poznámka'));
        $form->addHidden('event_id', $this->getParam('id'));
        $_this = $this;
        $form->addSubmit('submit', _('Ulozit'))->onClick[] = function () use ($_this, $form) {
            $_this->orgFormSuccess($form);
        };
        // $form->onSuccess[] = [$this, 'orgFormSuccess'];
        return $control;
    }

    public function orgFormSuccess(Form $form) {
        $values = $form->getValues();
        Debugger::barDump($values);
        $model = $this->serviceEventOrg->createNew([
            'person_id' => $values[ExtendedPersonHandler::CONT_AGGR]['person_id'],
            'event_id' => $values->event_id,
            'note' => $values->note
        ]);
        $this->serviceEventOrg->save($model);
        $this->flashMessage(_('Organizátor bol založený'), 'success');
        $this->redirect('this');
    }
}
