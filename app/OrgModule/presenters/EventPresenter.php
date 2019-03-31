<?php

namespace OrgModule;

use Events\Model\ApplicationHandlerFactory;
use Events\Model\Grid\SingleEventSource;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Events\ApplicationsGrid;
use FKSDB\Components\Events\ExpressionPrinter;
use FKSDB\Components\Events\GraphComponent;
use FKSDB\Components\Events\ImportComponent;
use FKSDB\Components\Forms\Factories\EventFactory;
use FKSDB\Components\Grids\Events\EventsGrid;
use FKSDB\Components\Grids\Events\LayoutResolver;
use FKSDB\Config\NeonScheme;
use FKSDB\Logging\FlashDumpFactory;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelAuthToken;
use FKSDB\ORM\Services\ServiceAuthToken;
use FKSDB\ORM\Services\ServiceEvent;
use FormUtils;
use ModelException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Neon\Neon;
use Nette\NotImplementedException;
use Nette\Utils\Html;
use Nette\Utils\NeonException;
use Tracy\Debugger;
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
     * @var \FKSDB\ORM\Services\ServiceEvent
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
     * @var Container
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
     * @var \FKSDB\ORM\Services\ServiceAuthToken $serviceAuthToken
     */
    private $serviceAuthToken;

    /**
     * @param ServiceAuthToken $serviceAuthToken
     */
    public function injectServiceAuthToken(ServiceAuthToken $serviceAuthToken) {
        $this->serviceAuthToken = $serviceAuthToken;
    }


    /**
     * @param \FKSDB\ORM\Services\ServiceEvent $serviceEvent
     */
    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    /**
     * @param EventFactory $eventFactory
     */
    public function injectEventFactory(EventFactory $eventFactory) {
        $this->eventFactory = $eventFactory;
    }

    /**
     * @param LayoutResolver $layoutResolver
     */
    public function injectLayoutResolver(LayoutResolver $layoutResolver) {
        $this->layoutResolver = $layoutResolver;
    }

    /**
     * @param Container $container
     */
    public function injectContainer(Container $container) {
        $this->container = $container;
    }

    /**
     * @param ExpressionPrinter $expressionPrinter
     */
    public function injectExpressionPrinter(ExpressionPrinter $expressionPrinter) {
        $this->expressionPrinter = $expressionPrinter;
    }

    /**
     * @param ApplicationHandlerFactory $handlerFactory
     */
    public function injectHandlerFactory(ApplicationHandlerFactory $handlerFactory) {
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @param FlashDumpFactory $flashDumpFactory
     */
    public function injectFlashDumpFactory(FlashDumpFactory $flashDumpFactory) {
        $this->flashDumpFactory = $flashDumpFactory;
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function authorizedApplications($id) {
        $model = $this->getModel();
        if (!$model) {
            throw new BadRequestException('Neexistující model.', 404);
        }
        $this->setAuthorized($this->getContestAuthorizator()
            ->isAllowed($model, 'application', $this->getSelectedContest()));
    }

    public function titleList() {
        $this->setTitle(_('Akce'));
        $this->setIcon('fa fa-calendar-check-o');
    }

    public function titleCreate() {
        $this->setTitle(_('Přidat akci'));
        $this->setIcon('fa fa-calendar-plus-o');
    }

    public function titleEdit() {
        $model = $this->getModel();
        $this->setTitle(sprintf(_('Úprava akce %s'), $model->name));
        $this->setIcon('fa fa-pencil');
    }

    public function titleApplications() {
        $model = $this->getModel();
        $this->setTitle(sprintf(_('Přihlášky akce %s'), $model->name));
        $this->setIcon('fa fa-calendar-check-o');
    }

    public function titleModel() {
        $model = $this->getModel();
        $this->setTitle(sprintf(_('Model akce %s'), $model->name));
        $this->setIcon('fa fa-cubes');
    }

    public function actionDelete() {
// There's no use case for this. (Errors must be deleted manually via SQL.)
        throw new NotImplementedException(null, 501);
    }

    /**
     * @param $id
     */
    public function renderApplications($id) {
        $this->template->event = $this->getModel();
    }

    /**
     * @param $name
     * @return FormControl|mixed
     * @throws BadRequestException
     */
    protected function createComponentCreateComponent($name) {
        $control = $this->createForm();
        $form = $control->getForm();

        $form->addSubmit('send', _('Přidat'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleFormSuccess($form, true);
        };

        return $control;
    }

    /**
     * @param $name
     * @return FormControl|mixed
     * @throws BadRequestException
     */
    protected function createComponentEditComponent($name) {
        $control = $this->createForm();
        $form = $control->getForm();
        $form->addSubmit('send', _('Uložit'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleFormSuccess($form, false);
        };

        return $control;
    }

    /**
     * @param $name
     * @return EventsGrid|mixed
     */
    protected function createComponentGrid($name) {
        return new EventsGrid($this->serviceEvent);
    }

    /**
     * @param $name
     * @return ApplicationsGrid
     */
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

    /**
     * @param $name
     * @return ImportComponent
     */
    protected function createComponentApplicationsImport($name) {
        $source = new SingleEventSource($this->getModel(), $this->container);
        $logger = new MemoryLogger(); //TODO log to file?
        $machine = $this->container->createEventMachine($this->getModel());
        $handler = $this->handlerFactory->create($this->getModel(), $logger);

        $flashDump = $this->flashDumpFactory->createApplication();
        $component = new ImportComponent($machine, $source, $handler, $flashDump, $this->container);
        return $component;
    }

    /**
     * @param $name
     * @return GraphComponent
     */
    protected function createComponentGraphComponent($name) {
        $event = $this->getModel();
        $machine = $this->container->createEventMachine($event);

        $component = new GraphComponent($machine->getPrimaryMachine(), $this->expressionPrinter);
        return $component;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    private function createForm() {
        $control = new FormControl();
        $form = $control->getForm();

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
                        $parameters = [];
                    }

                    NeonScheme::readSection($parameters, $scheme);
                    return true;
                } catch (NeonException $exception) {
                    $control->addError($exception->getMessage());
                    return false;
                }
            }, _('Parametry nesplňují Neon schéma'));
        }

        return $control;
    }

    /**
     * @param $scheme
     * @return Html
     */
    private function createParamDescription($scheme) {
        $result = Html::el('ul');
        foreach ($scheme as $key => $meta) {
            $item = Html::el('li');
            $result->addText($item);

            $item->addHtml(Html::el(null)->setText($key));
            if (isset($meta['default'])) {
                $item->addText(': ');
                $item->addHtml(Html::el(null)->setText(Utils::getRepr($meta['default'])));
            }
        }

        return $result;
    }

    /**
     * @param IModel|null $model
     * @param Form $form
     */
    protected function setDefaults(IModel $model = null, Form $form) {
        if (!$model) {
            return;
        }
        $defaults = array(
            self::CONT_EVENT => $model->toArray(),
        );
        $form->setDefaults($defaults);
    }

    /**
     * @param $id
     * @return \FKSDB\ORM\AbstractModelSingle|\Nette\Database\Table\ActiveRow|null
     */
    protected function loadModel($id) {
        return $this->serviceEvent->findByPrimary($id);
    }

    /**
     * @param Form $form
     * @param $isNew
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     * @throws \ReflectionException
     */
    private function handleFormSuccess(Form $form, $isNew) {
        $connection = $this->serviceEvent->getConnection();
        $values = $form->getValues();
        if ($isNew) {
            $model = $this->serviceEvent->createNew();
            $model->update(['year'=> $this->getSelectedYear()]);
        } else {
            $model = $this->getModel();
        }

        try {
            $connection->beginTransaction();
            /*
             * Event
             */
            $data = FormUtils::emptyStrToNull($values[self::CONT_EVENT]);
            $model->update($data);

            if (!$this->getContestAuthorizator()
                ->isAllowed($model, $isNew ? 'create' : 'edit', $this->getSelectedContest())
            ) {
                throw new ForbiddenRequestException();
            }

            $this->serviceEvent->save($model);

            // update also 'until' of authTokens in case that registration end has changed
            $tokenData = ["until" => $model->registration_end ?: $model->end];
            foreach ($this->serviceAuthToken->findTokensByEventId($model->id) as $row) {
                $token = ModelAuthToken::createFromTableRow($row);
                $token->update($tokenData);
                $this->serviceAuthToken->save($token);
            }

            /*
             * Finalize
             */
            $connection->commit();

            $this->flashMessage(sprintf(_('Akce %s uložena.'), $model->name), self::FLASH_SUCCESS);
            $this->backLinkRedirect();
            $this->redirect('list'); // if there's no backlink
        } catch (ModelException $exception) {
            $connection->rollBack();
            Debugger::log($exception, Debugger::ERROR);
            $this->flashMessage(_('Chyba přidání akce.'), self::FLASH_ERROR);
        } catch (ForbiddenRequestException $exception) {
            $connection->rollBack();
            $this->flashMessage(_('Nedostatečné oprávnění.'), self::FLASH_ERROR);
        }
    }

}
