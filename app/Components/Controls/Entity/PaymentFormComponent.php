<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Forms\Containers\PersonPaymentContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\Payment\CurrencyField;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Model\Exceptions\NotImplementedException;
use FKSDB\Model\ORM\Models\ModelLogin;
use FKSDB\Model\ORM\Models\ModelPayment;
use FKSDB\Model\ORM\Services\Schedule\ServiceSchedulePayment;
use FKSDB\Model\ORM\Services\ServicePayment;
use FKSDB\Model\Payment\Handler\DuplicatePaymentException;
use FKSDB\Model\Payment\Handler\EmptyDataException;
use FKSDB\Model\Payment\Transition\PaymentMachine;
use FKSDB\Model\Transitions\Transition\UnavailableTransitionsException;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * Class SelectForm
 * @author Michal Červeňák <miso@fykos.cz>
 * @property ModelPayment $model
 */
class PaymentFormComponent extends AbstractEntityFormComponent {
    private PersonFactory $personFactory;
    private PersonProvider $personProvider;
    private bool $isOrg;
    private PaymentMachine $machine;
    private ServicePayment $servicePayment;
    private ServiceSchedulePayment $serviceSchedulePayment;

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
        ServiceSchedulePayment $serviceSchedulePayment
    ): void {
        $this->servicePayment = $servicePayment;
        $this->personFactory = $personFactory;
        $this->personProvider = $personProvider;
        $this->serviceSchedulePayment = $serviceSchedulePayment;
    }

    protected function appendSubmitButton(Form $form): SubmitButton {
        return $form->addSubmit('submit', $this->isCreating() ? _('Proceed to summary') : _('Save payment'));
    }

    protected function configureForm(Form $form): void {
        if ($this->isOrg) {
            $form->addComponent($this->personFactory->createPersonSelect(true, _('Person'), $this->personProvider), 'person_id');
        } else {
            $form->addHidden('person_id');
        }
        $currencyField = new CurrencyField();
        $currencyField->setRequired(_('Please select currency'));
        $form->addComponent($currencyField, 'currency');
        $form->addComponent(new PersonPaymentContainer($this->getContext(), $this->machine->getEvent(), $this->machine->getScheduleGroupTypes(), !$this->isCreating()), 'payment_accommodation');
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     * @throws ForbiddenRequestException
     * @throws NotImplementedException
     * @throws UnavailableTransitionsException
     */
    protected function handleFormSuccess(Form $form): void {
        $values = $form->getValues();
        $data = [
            'currency' => $values['currency'],
            'person_id' => $values['person_id'],
        ];
        if (isset($this->model)) {
            $this->servicePayment->updateModel2($this->model, $data);
            $model = $this->servicePayment->refresh($this->model);
        } else {
            $model = $this->machine->createNewModel(array_merge($data, [
                'event_id' => $this->machine->getEvent()->event_id,
            ]), $this->servicePayment);
        }

        $connection = $this->servicePayment->getConnection();
        $connection->beginTransaction();

        try {
            $this->serviceSchedulePayment->store((array)$values['payment_accommodation'], $model);
            //$this->serviceSchedulePayment->prepareAndUpdate($values['payment_accommodation'], $model);
        } catch (DuplicatePaymentException | EmptyDataException $exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            $connection->rollBack();
            return;
        }
        $connection->commit();

        $this->getPresenter()->flashMessage(!isset($this->model) ? _('Payment has been created.') : _('Payment has been updated.'));
        $this->getPresenter()->redirect('detail', ['id' => $model->payment_id]);
    }

    /**
     * @return void
     * @throws BadTypeException
     */
    protected function setDefaults(): void {
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
