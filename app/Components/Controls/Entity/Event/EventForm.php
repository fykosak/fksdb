<?php

namespace FKSDB\Components\Controls\Entity\Event;

use FKSDB\Components\Controls\Entity\AbstractEntityFormControl;
use FKSDB\Components\Controls\Entity\IEditEntityForm;
use FKSDB\Components\Forms\Factories\EventFactory;
use FKSDB\Config\NeonSchemaException;
use FKSDB\Config\NeonScheme;
use FKSDB\Events\EventDispatchFactory;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Exceptions\ModelException;
use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceAuthToken;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;
use Nette\Neon\Neon;
use Nette\Utils\Html;
use Tracy\Debugger;

/**
 * Class AbstractForm
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventForm extends AbstractEntityFormControl implements IEditEntityForm {
    const CONT_EVENT = 'event';
    /**
     * @var ModelContest
     */
    protected $contest;
    /**
     * @var EventFactory
     */
    protected $eventFactory;
    /**
     * @var ServiceAuthToken
     */
    protected $serviceAuthToken;
    /**
     * @var ServiceEvent
     */
    protected $serviceEvent;

    /**
     * @var ModelEvent
     */
    protected $model;
    /**
     * @var int
     */
    private $year;
    /**
     * @var EventDispatchFactory
     */
    private $eventDispatchFactory;

    /**
     * AbstractForm constructor.
     * @param ModelContest $contest
     * @param Container $container
     * @param int $year
     * @param bool $create
     */
    public function __construct(ModelContest $contest, Container $container, int $year, bool $create) {
        parent::__construct($container, $create);
        $this->contest = $contest;
        $this->year = $year;
    }

    /**
     * @param EventFactory $eventFactory
     * @param ServiceAuthToken $serviceAuthToken
     * @param ServiceEvent $serviceEvent
     * @param EventDispatchFactory $eventDispatchFactory
     * @return void
     */
    public function injectPrimary(
        EventFactory $eventFactory,
        ServiceAuthToken $serviceAuthToken,
        ServiceEvent $serviceEvent,
        EventDispatchFactory $eventDispatchFactory
    ) {
        $this->serviceAuthToken = $serviceAuthToken;
        $this->eventFactory = $eventFactory;
        $this->serviceEvent = $serviceEvent;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    /**
     * @param Form $form
     * @return void
     * @throws \Exception
     */
    protected function configureForm(Form $form) {
        $eventContainer = $this->eventFactory->createEvent($this->contest);
        $form->addComponent($eventContainer, self::CONT_EVENT);
    }

    /**
     * @param ModelEvent|AbstractModelSingle $event
     */
    protected function updateTokens(ModelEvent $event) {
        $connection = $this->serviceAuthToken->getConnection();
        try {
            $connection->beginTransaction();
            // update also 'until' of authTokens in case that registration end has changed
            $tokenData = ['until' => $event->registration_end ?: $event->end];
            foreach ($this->serviceAuthToken->findTokensByEventId($event->event_id) as $token) {
                $this->serviceAuthToken->updateModel2($token, $tokenData);
            }
            $connection->commit();
        } catch (ModelException $exception) {
            $connection->rollBack();
            Debugger::log($exception, Debugger::ERROR);
            $this->flashMessage(_('Chyba přidání akce.'), ILogger::ERROR);
        }
    }

    /**
     * @param AbstractModelSingle|ModelEvent $model
     * @throws BadRequestException
     * @throws NeonSchemaException
     */
    public function setModel(AbstractModelSingle $model) {
        $this->model = $model;
        $this->getForm()->setDefaults([
            self::CONT_EVENT => $model->toArray(),
        ]);
        /** @var TextArea $paramControl */
        $paramControl = $this->getForm()->getComponent(self::CONT_EVENT)->getComponent('parameters');
        $holder = $this->eventDispatchFactory->getDummyHolder($this->model);
        $paramControl->setOption('description', $this->createParamDescription($holder));
        $paramControl->addRule(function (BaseControl $control) use ($holder) {

            $scheme = $holder->getPrimaryHolder()->getParamScheme();
            $parameters = $control->getValue();
            try {
                if ($parameters) {
                    $parameters = Neon::decode($parameters);
                } else {
                    $parameters = [];
                }
                NeonScheme::readSection($parameters, $scheme);
                return true;
            } catch (NeonSchemaException $exception) {
                $control->addError($exception->getMessage());
                return false;
            }
        }, _('Parametry nesplňují Neon schéma'));
    }


    /**
     * @param Holder $holder
     * @return Html
     */
    protected function createParamDescription(Holder $holder) {
        $scheme = $holder->getPrimaryHolder()->getParamScheme();
        $result = Html::el('ul');
        foreach ($scheme as $key => $meta) {
            $item = Html::el('li');
            $result->addText($item);

            $item->addHtml(Html::el(null)->setText($key));
            if (isset($meta['default'])) {
                $item->addText(': ');
                $item->addHtml(Html::el(null)->setText(\Utils::getRepr($meta['default'])));
            }
        }
        return $result;
    }

    /**
     * @param array $data
     * @return void
     * @throws AbortException
     */
    protected function handleEditSuccess(array $data) {
        $this->serviceEvent->updateModel2($this->model, $data);
        $this->updateTokens($this->model);
        $this->flashMessage(sprintf(_('Akce %s uložena.'), $this->model->name), ILogger::SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    /**
     * @param array $data
     * @return void
     * @throws AbortException
     */
    protected function handleCreateSuccess(array $data) {
        $data['year'] = $this->year;
        $model = $this->serviceEvent->createNewModel($data);

        $this->updateTokens($model);
        $this->flashMessage(sprintf(_('Akce %s uložena.'), $model->name), ILogger::SUCCESS);

        $this->getPresenter()->redirect('list'); // if there's no backlink
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    protected function handleFormSuccess(Form $form) {
        $values = $form->getValues();
        $data = \FormUtils::emptyStrToNull($values[self::CONT_EVENT], true);
        try {
            $this->create ? $this->handleCreateSuccess($data) : $this->handleEditSuccess($data);
        } catch (ModelException $exception) {
            Debugger::log($exception);
            $this->flashMessage(_('Error'), Message::LVL_DANGER);
        }
    }
}
