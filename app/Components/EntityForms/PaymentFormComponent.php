<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\PersonPaymentContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\Schedule\ServiceSchedulePayment;
use FKSDB\Models\ORM\Services\ServicePayment;
use FKSDB\Models\Payment\Handler\DuplicatePaymentException;
use FKSDB\Models\Payment\Handler\EmptyDataException;
use FKSDB\Models\Payment\Transition\PaymentMachine;
use FKSDB\Models\Submits\StorageException;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * @property ModelPayment|null $model
 */
class PaymentFormComponent extends EntityFormComponent
{
    private PersonFactory $personFactory;
    private PersonProvider $personProvider;
    private bool $isOrg;
    private PaymentMachine $machine;
    private ServicePayment $servicePayment;
    private ServiceSchedulePayment $serviceSchedulePayment;
    private SingleReflectionFormFactory $reflectionFormFactory;

    public function __construct(
        Container $container,
        bool $isOrg,
        PaymentMachine $machine,
        ?ModelPayment $model
    ) {
        parent::__construct($container, $model);
        $this->machine = $machine;
        $this->isOrg = $isOrg;
    }

    final public function injectPrimary(
        ServicePayment $servicePayment,
        PersonFactory $personFactory,
        PersonProvider $personProvider,
        ServiceSchedulePayment $serviceSchedulePayment,
        SingleReflectionFormFactory $reflectionFormFactory
    ): void {
        $this->servicePayment = $servicePayment;
        $this->personFactory = $personFactory;
        $this->personProvider = $personProvider;
        $this->serviceSchedulePayment = $serviceSchedulePayment;
        $this->reflectionFormFactory = $reflectionFormFactory;
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', $this->isCreating() ? _('Proceed to summary') : _('Save payment'));
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
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
                $this->machine->event,
                $this->machine->scheduleGroupTypes,
                !$this->isCreating()
            ),
            'payment_accommodation'
        );
    }

    /**
     * @throws ForbiddenRequestException
     * @throws NotImplementedException
     * @throws UnavailableTransitionsException
     * @throws ModelException
     * @throws StorageException
     */
    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues();
        $data = [
            'currency' => $values['currency'],
            'person_id' => $values['person_id'],
        ];

        if (isset($this->model)) {
            $this->servicePayment->updateModel($this->model, $data);
            $model = $this->model;
        } else {
            $holder = $this->machine->createHolder(null);
            $this->machine->saveAndExecuteImplicitTransition(
                $holder,
                array_merge($data, [
                    'event_id' => $this->machine->event->event_id,
                ])
            );
            $model = $holder->getModel();
        }

        $connection = $this->servicePayment->explorer->getConnection();
        $connection->beginTransaction();

        try {
            $this->serviceSchedulePayment->storeItems((array)$values['payment_accommodation'], $model); // TODO
            //$this->serviceSchedulePayment->prepareAndUpdate($values['payment_accommodation'], $model);
        } catch (DuplicatePaymentException | EmptyDataException $exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            $connection->rollBack();
            return;
        }
        $connection->commit();

        $this->getPresenter()->flashMessage(
            !isset($this->model) ? _('Payment has been created.') : _('Payment has been updated.')
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
            $values['payment_accommodation'] = $items;
            $this->getForm()->setDefaults($values);
        } else {
            /** @var ModelLogin $login */
            $login = $this->getPresenter()->getUser()->getIdentity();
            $this->getForm()->setDefaults([
                'person_id' => $login->getPerson()->person_id,
            ]);
        }
    }
}
