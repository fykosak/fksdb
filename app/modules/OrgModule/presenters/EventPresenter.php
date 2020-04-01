<?php

namespace OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\EventFactory;
use FKSDB\Components\Grids\Events\EventsGrid;
use FKSDB\Config\NeonScheme;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceAuthToken;
use FKSDB\ORM\Services\ServiceEvent;
use FormUtils;
use ModelException;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Neon\Neon;
use FKSDB\NotImplementedException;
use Nette\Utils\Html;
use Nette\Utils\NeonException;
use Tracy\Debugger;
use Utils;


/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * @author Michal Koutný <michal@fykos.cz>
 */
class EventPresenter extends EntityPresenter {

    const CONT_EVENT = 'event';

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * @var EventFactory
     */
    private $eventFactory;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var ServiceAuthToken $serviceAuthToken
     */
    private $serviceAuthToken;

    /**
     * @param ServiceAuthToken $serviceAuthToken
     */
    public function injectServiceAuthToken(ServiceAuthToken $serviceAuthToken) {
        $this->serviceAuthToken = $serviceAuthToken;
    }

    /**
     * @param ServiceEvent $serviceEvent
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
     * @param Container $container
     */
    public function injectContainer(Container $container) {
        $this->container = $container;
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

    /**
     * @throws NotImplementedException
     */
    public function actionDelete() {
// There's no use case for this. (Errors must be deleted manually via SQL.)
        throw new NotImplementedException(null);
    }

    /**
     * @return FormControl|mixed
     * @throws BadRequestException
     */
    protected function createComponentCreateComponent() {
        $control = $this->createForm();
        $form = $control->getForm();

        $form->addSubmit('send', _('Přidat'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleFormSuccess($form, true);
        };

        return $control;
    }

    /**
     * @return FormControl|mixed
     * @throws BadRequestException
     */
    protected function createComponentEditComponent() {
        $control = $this->createForm();
        $form = $control->getForm();
        $form->addSubmit('send', _('Save'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleFormSuccess($form, false);
        };

        return $control;
    }

    /**
     * @return EventsGrid
     */
    protected function createComponentGrid(): EventsGrid {
        return new EventsGrid($this->getContext());
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws \Exception
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
        $defaults = [
            self::CONT_EVENT => $model->toArray(),
        ];
        $form->setDefaults($defaults);
    }

    /**
     * @param int $id
     * @return AbstractModelSingle|ModelEvent|null
     */
    protected function loadModel($id) {
        $row = $this->serviceEvent->findByPrimary($id);
        if (!$row) {
            return null;
        }
        return ModelEvent::createFromActiveRow($row);
    }

    /**
     * @param Form $form
     * @param $isNew
     * @throws BadRequestException
     * @throws AbortException
     * @throws \ReflectionException
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

            if (!$this->getContestAuthorizator()
                ->isAllowed($model, $isNew ? 'create' : 'edit', $this->getSelectedContest())
            ) {
                throw new ForbiddenRequestException();
            }

            $this->serviceEvent->save($model);

            // update also 'until' of authTokens in case that registration end has changed
            $tokenData = ["until" => $model->registration_end ?: $model->end];
            foreach ($this->serviceAuthToken->findTokensByEventId($model->id) as $token) {
                $this->serviceAuthToken->updateModel($token, $tokenData);
                $this->serviceAuthToken->save($token);
            }

            /*
             * Finalize
             */
            if (!$connection->commit()) {
                throw new ModelException();
            }

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

    /**
     * @inheritDoc
     */
    protected function getModelResource(): string {
        return 'event';
    }
}
