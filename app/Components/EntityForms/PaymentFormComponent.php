<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\PersonPaymentContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\ORM\Services\Schedule\SchedulePaymentService;
use FKSDB\Models\Submits\StorageException;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use Fykosak\NetteORM\Exceptions\ModelException;
use Nette\DI\Container;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Security\User;

/**
 * @property PaymentModel|null $model
 */
class PaymentFormComponent extends EntityFormComponent
{
    private PersonFactory $personFactory;
    private PersonProvider $personProvider;
    private bool $isOrg;
    private PaymentMachine $machine;
    private PaymentService $paymentService;
    private SchedulePaymentService $schedulePaymentService;
    private SingleReflectionFormFactory $reflectionFormFactory;
    private EventModel $event;
    private User $user;

    public function __construct(
        Container $container,
        bool $isOrg,
        PaymentMachine $machine,
        EventModel $event,
        ?PaymentModel $model
    ) {
        parent::__construct($container, $model);
        $this->machine = $machine;
        $this->event = $event;
        $this->isOrg = $isOrg;
    }

    final public function injectPrimary(
        User $user,
        PaymentService $paymentService,
        PersonFactory $personFactory,
        PersonProvider $personProvider,
        SchedulePaymentService $schedulePaymentService,
        SingleReflectionFormFactory $reflectionFormFactory
    ): void {
        $this->user = $user;
        $this->paymentService = $paymentService;
        $this->personFactory = $personFactory;
        $this->personProvider = $personProvider;
        $this->schedulePaymentService = $schedulePaymentService;
        $this->reflectionFormFactory = $reflectionFormFactory;
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', $this->isCreating() ? _('Proceed to summary') : _('Save payment'));
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws NotImplementedException
     */
    protected function configureForm(Form $form): void
    {
        if ($this->isOrg) {
            $form->addComponent(
                $this->personFactory->createPersonSelect(true, _('Person'), $this->personProvider),
                'person_id'
            );
        } else {
            $form->addHidden('person_id');
        }
        /** @var SelectBox $currencyField */
        $currencyField = $this->reflectionFormFactory->createField('payment', 'currency');
        // $currencyField->setItems($this->machine->priceCalculator->getAllowedCurrencies());
        $currencyField->setRequired(_('Please select currency'));
        $form->addComponent($currencyField, 'currency');
        $form->addComponent(
            new PersonPaymentContainer(
                $this->getContext(),
                $this->machine,
                $this->event,
                $this->user,
                $this->isOrg,
                !$this->isCreating()
            ),
            'items'
        );
    }

    /**
     * @throws UnavailableTransitionsException
     * @throws ModelException
     * @throws StorageException
     * @throws \Throwable
     */
    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues('array');
        $data = [
            'currency' => $values['currency'],
            'person_id' => $values['person_id'],
        ];
        $connection = $this->paymentService->explorer->getConnection();
        $connection->beginTransaction();
        try {
            $model = $this->paymentService->storeModel(
                array_merge($data, [
                    'event_id' => $this->event->event_id,
                ]),
                $this->model
            );
            $this->schedulePaymentService->storeItems((array)$values['items'], $model);
            if (!isset($this->model)) {
                $holder = $this->machine->createHolder($model);
                $this->machine->executeImplicitTransition($holder);
                $model = $holder->getModel();
            }
        } catch (\Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }
        $connection->commit();
        $this->getPresenter()->flashMessage(
            !isset($this->model)
                ? _('Payment has been created.')
                : _('Payment has been updated.')
        );
        $this->getPresenter()->redirect('detail', ['id' => $model->payment_id]);
    }

    /**
     * @throws BadTypeException
     */
    protected function setDefaults(): void
    {
        if (isset($this->model)) {
            $values = $this->model->toArray();
            $query = $this->model->getRelatedPersonSchedule();
            $items = [];
            foreach ($query as $row) {
                $key = 'person' . $row->person_id;
                $items[$key] = $items[$key] ?? [];
                $items[$key][$row->person_schedule_id] = true;
            }
            $values['items'] = $items;
            $this->getForm()->setDefaults($values);
        } else {
            /** @var LoginModel $login */
            $login = $this->getPresenter()->getUser()->getIdentity();
            $this->getForm()->setDefaults([
                'person_id' => $login->person->person_id,
            ]);
        }
    }
}
