<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Forms\Controls\Payment\CurrencyField;
use FKSDB\Components\Forms\Controls\Payment\PaymentSelectField;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\ORM\Services\Schedule\ServiceSchedulePayment;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\Handler\DuplicatePaymentException;
use FKSDB\Payment\Handler\EmptyDataException;
use FKSDB\Payment\Transition\PaymentMachine;
use FKSDB\Transitions\UnavailableTransitionsException;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * Class SelectForm
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PaymentFormComponent extends AbstractEntityFormComponent implements IEditEntityForm {

    /** @var PersonFactory */
    private $personFactory;

    /** @var PersonProvider */
    private $personProvider;

    /** @var ServicePersonSchedule */
    private $servicePersonSchedule;

    /** @var bool */
    private $isOrg;

    /** @var PaymentMachine */
    private $machine;

    /** @var ModelPayment */
    private $model;

    /** @var ServicePayment */
    private $servicePayment;

    /** @var ServiceSchedulePayment */
    private $serviceSchedulePayment;

    /**
     * SelectForm constructor.
     * @param Container $container
     * @param bool $isOrg
     * @param PaymentMachine $machine
     * @param bool $create
     */
    public function __construct(
        Container $container,
        bool $isOrg,
        PaymentMachine $machine,
        bool $create
    ) {
        parent::__construct($container, $create);
        $this->machine = $machine;
        $this->isOrg = $isOrg;
    }

    /**
     * @param ServicePayment $servicePayment
     * @param PersonFactory $personFactory
     * @param PersonProvider $personProvider
     * @param ServicePersonSchedule $servicePersonSchedule
     * @param ServiceSchedulePayment $serviceSchedulePayment
     * @return void
     */
    public function injectPrimary(
        ServicePayment $servicePayment,
        PersonFactory $personFactory,
        PersonProvider $personProvider,
        ServicePersonSchedule $servicePersonSchedule,
        ServiceSchedulePayment $serviceSchedulePayment
    ) {
        $this->servicePayment = $servicePayment;
        $this->personFactory = $personFactory;
        $this->personProvider = $personProvider;
        $this->servicePersonSchedule = $servicePersonSchedule;
        $this->serviceSchedulePayment = $serviceSchedulePayment;
    }

    /**
     * @param ModelPayment|AbstractModelSingle $modelPayment
     * @return void
     * @throws BadTypeException
     */
    public function setModel(AbstractModelSingle $modelPayment) {
        $this->model = $modelPayment;
        $values = $this->model->toArray();
        $values['payment_accommodation'] = $this->serializeScheduleValue();
        $this->getForm()->setDefaults($values);
    }

    /**
     * @return void
     * @throws BadTypeException
     */
    public function render() {
        if ($this->create) {
            /** @var ModelLogin $login */
            $login = $this->getPresenter()->getUser()->getIdentity();
            $this->getForm()->setDefaults([
                'person_id' => $login->getPerson()->person_id,
            ]);
        }
        parent::render();
    }

    private function serializeScheduleValue(): string {
        $query = $this->model->getRelatedPersonSchedule();
        $items = [];
        foreach ($query as $row) {
            $items[$row->person_schedule_id] = true;
        }
        return \json_encode($items);
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     * @throws ForbiddenRequestException
     * @throws NotImplementedException
     * @throws UnavailableTransitionsException
     */
    protected function handleFormSuccess(Form $form) {
        $values = $form->getValues();
        $data = [
            'currency' => $values['currency'],
            'person_id' => $values['person_id'],
        ];
        if ($this->create) {
            $model = $this->machine->createNewModel(array_merge($data, [
                'event_id' => $this->machine->getEvent()->event_id,
            ]), $this->servicePayment);

        } else {
            $model = $this->model;
            $this->servicePayment->updateModel2($model, $data);
        }

        $connection = $this->servicePayment->getConnection();
        $connection->beginTransaction();

        try {
            $this->serviceSchedulePayment->prepareAndUpdate($values['payment_accommodation'], $model);
        } catch (DuplicatePaymentException $exception) {
            $this->flashMessage($exception->getMessage(), BasePresenter::FLASH_ERROR);
            $connection->rollBack();
            return;
        } catch (EmptyDataException $exception) {
            $this->flashMessage($exception->getMessage(), BasePresenter::FLASH_ERROR);
            $connection->rollBack();
            return;
        }
        $connection->commit();

        $this->getPresenter()->flashMessage($this->create ? _('Payment has been created.') : _('Payment has been updated.'));
        $this->getPresenter()->redirect('detail', ['id' => $model->payment_id]);
    }

    /**
     * @param Form $form
     * @return void
     * @throws BadRequestException
     */
    protected function configureForm(Form $form) {
        if ($this->isOrg) {
            $form->addComponent($this->personFactory->createPersonSelect(true, _('Person'), $this->personProvider), 'person_id');
        } else {
            $form->addHidden('person_id');
        }
        $currencyField = new CurrencyField();
        $currencyField->setRequired(_('Please select currency'));
        $form->addComponent($currencyField, 'currency');
        $form->addComponent(new PaymentSelectField($this->servicePersonSchedule, $this->machine->getEvent(), $this->machine->getScheduleGroupTypes(), !$this->create), 'payment_accommodation');
    }

    protected function appendSubmitButton(Form $form): SubmitButton {
        return $form->addSubmit('submit', $this->create ? _('Proceed to summary') : _('Save payment'));
    }
}
