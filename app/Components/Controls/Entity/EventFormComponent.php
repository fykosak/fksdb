<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Config\NeonSchemaException;
use FKSDB\Config\NeonScheme;

use FKSDB\Models\DBReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Models\DBReflection\OmittedControlException;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\ServiceAuthToken;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\Utils\FormUtils;
use FKSDB\Models\Utils\Utils;
use Fykosak\Utils\Logging\Message;

use Nette\Application\AbortException;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Form;
use Nette\Neon\Neon;
use Nette\Utils\Html;

/**
 * Class AbstractForm
 * @author Michal Červeňák <miso@fykos.cz>
 * @property ModelEvent $model
 */
class EventFormComponent extends AbstractEntityFormComponent {
    public const CONT_EVENT = 'event';

    private ModelContest $contest;
    private SingleReflectionFormFactory $singleReflectionFormFactory;
    private ServiceAuthToken $serviceAuthToken;
    private ServiceEvent $serviceEvent;
    private int $year;
    private EventDispatchFactory $eventDispatchFactory;

    public function __construct(ModelContest $contest, Container $container, int $year, ?ModelEvent $model) {
        parent::__construct($container, $model);
        $this->contest = $contest;
        $this->year = $year;
    }

    final public function injectPrimary(
        SingleReflectionFormFactory $singleReflectionFormFactory,
        ServiceAuthToken $serviceAuthToken,
        ServiceEvent $serviceEvent,
        EventDispatchFactory $eventDispatchFactory
    ): void {
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
    protected function configureForm(Form $form): void {
        $eventContainer = $this->createEventContainer();
        $form->addComponent($eventContainer, self::CONT_EVENT);
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    protected function handleFormSuccess(Form $form): void {
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull($values[self::CONT_EVENT], true);
        $data['year'] = $this->year;
        $model = $this->serviceEvent->store($this->model ?? null, $data);
        $this->updateTokens($model);
        $this->flashMessage(sprintf(_('Event "%s" has been saved.'), $model->name), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    /**
     * @return void
     * @throws BadTypeException
     * @throws NeonSchemaException
     */
    protected function setDefaults(): void {
        if (isset($this->model)) {
            $this->getForm()->setDefaults([
                self::CONT_EVENT => $this->model->toArray(),
            ]);
            /** @var TextArea $paramControl */
            $paramControl = $this->getForm()->getComponent(self::CONT_EVENT)->getComponent('parameters');
            $holder = $this->eventDispatchFactory->getDummyHolder($this->model);
            $paramControl->setOption('description', $this->createParamDescription($holder));
            $paramControl->addRule(function (BaseControl $control) use ($holder): bool {

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
    }

    /**
     * @throws AbstractColumnException
     * @throws OmittedControlException
     * @throws BadTypeException
     */
    private function createEventContainer(): ModelContainer {
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

    private function createParamDescription(Holder $holder): Html {
        $scheme = $holder->getPrimaryHolder()->getParamScheme();
        $result = Html::el('ul');
        foreach ($scheme as $key => $meta) {
            $item = Html::el('li');
            $result->addText($item);

            $item->addHtml(Html::el()->setText($key));
            if (isset($meta['default'])) {
                $item->addText(': ');
                $item->addHtml(Html::el()->setText(Utils::getRepresentation($meta['default'])));
            }
        }
        return $result;
    }

    private function updateTokens(ModelEvent $event): void {
        $connection = $this->serviceAuthToken->getConnection();
        $connection->beginTransaction();
        // update also 'until' of authTokens in case that registration end has changed
        $tokenData = ['until' => $event->registration_end ?: $event->end];
        foreach ($this->serviceAuthToken->findTokensByEventId($event->event_id) as $token) {
            $this->serviceAuthToken->updateModel2($token, $tokenData);
        }
        $connection->commit();
    }

}
