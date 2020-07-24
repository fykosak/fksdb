<?php

namespace FKSDB\Components\Controls\Entity\Event;

use FKSDB\Components\Controls\Entity\AbstractEntityFormComponent;
use FKSDB\Components\Controls\Entity\IEditEntityForm;
use FKSDB\DBReflection\ColumnFactories\AbstractColumnException;
use FKSDB\DBReflection\OmittedControlException;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Config\NeonSchemaException;
use FKSDB\Config\NeonScheme;
use FKSDB\Events\EventDispatchFactory;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\ModelException;
use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceAuthToken;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\Utils\FormUtils;
use FKSDB\Utils\Utils;
use Nette\Application\AbortException;
use Nette\Forms\Form;
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
class EventFormComponent extends AbstractEntityFormComponent implements IEditEntityForm {
    const CONT_EVENT = 'event';

    /** @var ModelContest */
    protected $contest;

    /** @var SingleReflectionFormFactory */
    protected $singleReflectionFormFactory;

    /** @var ServiceAuthToken */
    protected $serviceAuthToken;

    /** @var ServiceEvent */
    protected $serviceEvent;

    /** @var ModelEvent */
    protected $model;

    /** @var int */
    private $year;

    /** @var EventDispatchFactory */
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
     * @param SingleReflectionFormFactory $singleReflectionFormFactory
     * @param ServiceAuthToken $serviceAuthToken
     * @param ServiceEvent $serviceEvent
     * @param EventDispatchFactory $eventDispatchFactory
     * @return void
     */
    public function injectPrimary(
        SingleReflectionFormFactory $singleReflectionFormFactory,
        ServiceAuthToken $serviceAuthToken,
        ServiceEvent $serviceEvent,
        EventDispatchFactory $eventDispatchFactory
    ) {
        $this->serviceAuthToken = $serviceAuthToken;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
        $this->serviceEvent = $serviceEvent;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbstractColumnException
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form) {
        $eventContainer = $this->createEventContainer();
        $form->addComponent($eventContainer, self::CONT_EVENT);
    }

    /**
     * @param ModelEvent $event
     * @return void
     */
    protected function updateTokens(ModelEvent $event) {
        $connection = $this->serviceAuthToken->getConnection();
        $connection->beginTransaction();
        // update also 'until' of authTokens in case that registration end has changed
        $tokenData = ['until' => $event->registration_end ?: $event->end];
        foreach ($this->serviceAuthToken->findTokensByEventId($event->event_id) as $token) {
            $this->serviceAuthToken->updateModel2($token, $tokenData);
        }
        $connection->commit();
    }

    /**
     * @param AbstractModelSingle $model
     * @return void
     * @throws BadTypeException
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
        }, _('Parameters does not fulfill the Neon scheme'));
    }

    protected function createParamDescription(Holder $holder): Html {
        $scheme = $holder->getPrimaryHolder()->getParamScheme();
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
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    protected function handleFormSuccess(Form $form) {
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull($values[self::CONT_EVENT], true);
        if ($this->create) {
            $data['year'] = $this->year;
            $model = $this->serviceEvent->createNewModel($data);
        } else {
            $this->serviceEvent->updateModel2($this->model, $data);
            $model = $this->model;
        }
        $this->updateTokens($model);
        $this->flashMessage(sprintf(_('Event "%s" has been saved.'), $model->name), ILogger::SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    /**
     * @throws AbstractColumnException
     * @throws OmittedControlException
     * @throws BadTypeException
     */
    public function createEventContainer(): ModelContainer {
        return $this->singleReflectionFormFactory->createContainer('event', [
            'event_type_id',
            'event_year',
            'name',
            'begin',
            'end',
            'registration_begin',
            'registration_end',
            'report',
            'parameters',
        ], $this->contest);
    }
}
