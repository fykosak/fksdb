<?php

declare(strict_types=1);

namespace FKSDB\Components\Payments;

use FKSDB\Components\EntityForms\ModelForm;
use FKSDB\Components\EntityForms\Processing\DefaultTransition;
use FKSDB\Components\Forms\Containers\PersonPaymentContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonSelectBox;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\ReflectionFactory;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\ORM\Services\Schedule\SchedulePaymentService;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use Fykosak\NetteORM\Model\Model;
use Nette\DI\Container;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * @phpstan-extends ModelForm<PaymentModel,array{
 *      currency:string,
 *      person_id:int,
 *      want_invoice:bool,
 *      items_container:array<array<int,bool>>}>
 */
class SchedulePaymentForm extends ModelForm
{
    private const ItemsContainer = 'items_container';//phpscs:ignore

    private bool $isOrganizer;
    private PaymentMachine $machine;
    private PaymentService $paymentService;
    private SchedulePaymentService $schedulePaymentService;
    private ReflectionFactory $reflectionFormFactory;
    private EventModel $source;
    private PersonModel $loggedPerson;

    public function __construct(
        Container $container,
        EventModel $source,
        PersonModel $loggedPerson,
        bool $isOrganizer,
        PaymentMachine $machine,
        ?PaymentModel $model
    ) {
        parent::__construct($container, $model);
        $this->machine = $machine;
        $this->isOrganizer = $isOrganizer;
        $this->source = $source;
        $this->loggedPerson = $loggedPerson;
    }

    final public function injectPrimary(
        PaymentService $paymentService,
        SchedulePaymentService $schedulePaymentService,
        ReflectionFactory $reflectionFormFactory
    ): void {
        $this->paymentService = $paymentService;
        $this->schedulePaymentService = $schedulePaymentService;
        $this->reflectionFormFactory = $reflectionFormFactory;
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', isset($this->model) ? _('Save payment') : _('Proceed to summary'));
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws NotImplementedException
     * @throws \Exception
     */
    protected function configureForm(Form $form): void
    {
        if ($this->isOrganizer) {
            $form->addComponent(
                new PersonSelectBox(true, new PersonProvider($this->container), _('Person')),
                'person_id'
            );
        }
        /** @var SelectBox $currencyField */
        $currencyField = $this->reflectionFormFactory->createField('payment', 'currency');

        // $currencyField->setItems($this->machine->priceCalculator->getAllowedCurrencies());
        $currencyField->setRequired(_('Please select currency'));
        $form->addComponent($currencyField, 'currency');
       // $form->addComponent($this->reflectionFormFactory->createField('payment', 'want_invoice'), 'want_invoice');
        $form->addComponent(
            new PersonPaymentContainer(
                $this->getContext(),
                $this->source,
                $this->loggedPerson,
                $this->isOrganizer,
                $this->model
            ),
            self::ItemsContainer
        );
    }

    /**
     * @throws \Throwable
     */
    protected function innerSuccess(array $values, Form $form): Model
    {
        $model = $this->paymentService->storeModel(
            [
                'currency' => $values['currency'],
                'want_invoice' => $values['want_invoice'],
                'person_id' => $this->isOrganizer ? $values['person_id'] : $this->loggedPerson->person_id,
            ],
            $this->model
        );
        $this->schedulePaymentService->storeItems(
            (array)$values[self::ItemsContainer],
            $model,
            $this->translator
        );
        return $model;
    }

    protected function getPostprocessing(): array
    {
        $processing = parent::getPostprocessing();
        if (!isset($this->model)) {
            $processing[] = new DefaultTransition($this->container, $this->machine); //@phpstan-ignore-line
        }
        return $processing;
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(
            !isset($this->model)
                ? _('Payment has been created.')
                : _('Payment has been updated.')
        );
        $this->getPresenter()->redirect('detail', ['id' => $model->payment_id]);
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults($this->model->toArray());
            /** @var PersonPaymentContainer $itemContainer */
            $itemContainer = $form->getComponent(self::ItemsContainer);
            $itemContainer->setPayment($this->model);
        }
    }
}
