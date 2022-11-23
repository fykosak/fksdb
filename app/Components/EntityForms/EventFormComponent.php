<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EventService;
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
 * @property EventModel|null $model
 */
class EventFormComponent extends EntityFormComponent
{
    public const CONT_EVENT = 'event';

    private ContestYearModel $contestYear;
    private SingleReflectionFormFactory $singleReflectionFormFactory;
    private AuthTokenService $authTokenService;
    private EventService $eventService;
    private EventDispatchFactory $eventDispatchFactory;

    public function __construct(ContestYearModel $contestYear, Container $container, ?EventModel $model)
    {
        parent::__construct($container, $model);
        $this->contestYear = $contestYear;
    }

    final public function injectPrimary(
        SingleReflectionFormFactory $singleReflectionFormFactory,
        AuthTokenService $authTokenService,
        EventService $eventService,
        EventDispatchFactory $eventDispatchFactory
    ): void {
        $this->authTokenService = $authTokenService;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
        $this->eventService = $eventService;
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

    /**
     * @return never
     */
    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull2($values[self::CONT_EVENT]);
        $data['year'] = $this->contestYear->year;
        $model = $this->eventService->storeModel($data, $this->model);
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
                $parameters = $control->getValue();
                try {
                    if ($parameters) {
                        Neon::decode($parameters);
                    }
                    return true;
                } catch (\Throwable $exception) {
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
        return $this->singleReflectionFormFactory->createContainerWithMetadata('event', [
            'event_type_id' => ['required' => true],
            'event_year' => ['required' => true],
            'name' => ['required' => true],
            'begin' => ['required' => true],
            'end' => ['required' => true],
            'registration_begin' => ['required' => false],
            'registration_end' => ['required' => false],
            'report' => ['required' => false],
            'parameters' => ['required' => false],
        ], null, $this->contestYear->contest);
    }

    private function createParamDescription(BaseHolder $holder): Html
    {
        $scheme = $holder->paramScheme;
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

    private function updateTokens(EventModel $event): void
    {
        $connection = $this->authTokenService->explorer->getConnection();
        $connection->beginTransaction();
        // update also 'until' of authTokens in case that registration end has changed
        $tokenData = ['until' => $event->registration_end ?? $event->end];
        /** @var AuthTokenModel $token $token */
        foreach ($this->authTokenService->findTokensByEvent($event) as $token) {
            $this->authTokenService->storeModel($tokenData, $token);
        }
        $connection->commit();
    }
}
