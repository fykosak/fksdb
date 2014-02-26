<?php

namespace OrgModule;

use Events\Model\Grid\SingleEventSource;
use FKS\Config\NeonScheme;
use FKSDB\Components\Events\ApplicationsGrid;
use FKSDB\Components\Events\ExpressionPrinter;
use FKSDB\Components\Events\GraphComponent;
use FKSDB\Components\Forms\Factories\EventFactory;
use FKSDB\Components\Grids\Events\EventsGrid;
use FKSDB\Components\Grids\Events\LayoutResolver;
use FormUtils;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
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
use ServiceEvent;
use SystemContainer;
use Utils;

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

    /**
     * @var ExpressionPrinter
     */
    private $expressionPrinter;

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

    public function injectExpressionPrinter(ExpressionPrinter $expressionPrinter) {
        $this->expressionPrinter = $expressionPrinter;
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
        $form->onSuccess[] = function(Form $form) use($that) {
                    $that->handleFormSuccess($form, true);
                };

        return $form;
    }

    protected function createComponentEditComponent($name) {
        $form = $this->createForm();

        $form->addSubmit('send', _('Uložit'));
        $that = $this;
        $form->onSuccess[] = function(Form $form) use($that) {
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

        $grid = new ApplicationsGrid($this->container, $source);
        $template = $this->layoutResolver->getTableLayout($this->getModel());
        $grid->setTemplate($template);

        return $grid;
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
            $scheme = $holder->getParamScheme();
            $paramControl = $eventContainer->getComponent('parameters');
            $paramControl->setOption('description', $this->createParamDescription($scheme));
            $paramControl->addRule(function(BaseControl $control) use($scheme) {
                        $parameters = $control->getValue();
                        try {
                            $parameters = Neon::decode($parameters);
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

}
