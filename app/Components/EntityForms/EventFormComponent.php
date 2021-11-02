<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Expressions\NeonScheme;
use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\ServiceAuthToken;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\Utils\FormUtils;
use FKSDB\Models\Utils\Utils;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Form;
use Nette\Neon\Neon;
use Nette\Utils\Html;

/**
 * @property ModelEvent|null $model
 */
class EventFormComponent extends AbstractEntityFormComponent
{

    public const CONT_EVENT = 'event';

    private ModelContestYear $contestYear;
    private SingleReflectionFormFactory $singleReflectionFormFactory;
    private ServiceAuthToken $serviceAuthToken;
    private ServiceEvent $serviceEvent;
    private EventDispatchFactory $eventDispatchFactory;

    public function __construct(ModelContestYear $contestYear, Container $container, ?ModelEvent $model)
    {
        parent::__construct($container, $model);
        $this->contestYear = $contestYear;
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
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
        $eventContainer = $this->createEventContainer();
        $form->addComponent($eventContainer, self::CONT_EVENT);
    }

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull($values[self::CONT_EVENT], true);
        $data['year'] = $this->contestYear->year;
        /** @var ModelEvent $model */
        $model = $this->serviceEvent->storeModel($data, $this->model);
        $this->updateTokens($model);
        $this->flashMessage(sprintf(_('Event "%s" has been saved.'), $model->name), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    /**
     * @throws BadTypeException
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     */
    protected function setDefaults(): void
    {
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
            }, _('Parameters do not fulfill the Neon scheme'));
        }
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    private function createEventContainer(): ModelContainer
    {
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
        ], $this->contestYear->getContest());
    }

    private function createParamDescription(Holder $holder): Html
    {
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

    private function updateTokens(ModelEvent $event): void
    {
        $connection = $this->serviceAuthToken->explorer->getConnection();
        $connection->beginTransaction();
        // update also 'until' of authTokens in case that registration end has changed
        $tokenData = ['until' => $event->registration_end ?? $event->end];
        foreach ($this->serviceAuthToken->findTokensByEventId($event->event_id) as $token) {
            $this->serviceAuthToken->updateModel($token, $tokenData);
        }
        $connection->commit();
    }
}
