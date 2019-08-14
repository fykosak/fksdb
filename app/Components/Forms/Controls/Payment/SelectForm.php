<?php


namespace FKSDB\Components\Forms\Controls\Payment;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServiceEventPersonAccommodation;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\ORM\Services\ServicePaymentAccommodation;
use FKSDB\Payment\Handler\DuplicateAccommodationPaymentException;
use FKSDB\Payment\Handler\EmptyDataException;
use FKSDB\Payment\Transition\PaymentMachine;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class SelectForm
 * @package FKSDB\Components\Forms\Factories\Payment
 * @property FileTemplate $template
 */
class SelectForm extends Control {
    /**
     * @var PersonFactory
     */
    private $personFactory;
    /**
     * @var PersonProvider
     */
    private $personProvider;
    /**
     * @var ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;
    /**
     * @var \FKSDB\ORM\Models\ModelEvent
     */
    private $event;
    /**
     * @var bool
     */
    private $isOrg;
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * @var PaymentMachine
     */
    private $machine;
    /**
     * @var \FKSDB\ORM\Models\ModelPayment
     */
    private $model;
    /**
     * @var ServicePayment
     */
    private $servicePayment;
    /**
     * @var ServicePaymentAccommodation
     */
    private $servicePaymentAccommodation;

    /**
     * SelectForm constructor.
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @param bool $isOrg
     * @param ITranslator $translator
     * @param ServicePayment $servicePayment
     * @param PaymentMachine $machine
     * @param PersonFactory $personFactory
     * @param PersonProvider $personProvider
     * @param ServiceEventPersonAccommodation $serviceEventPersonAccommodation
     * @param ServicePaymentAccommodation $servicePaymentAccommodation
     */
    public function __construct(ModelEvent $event,
                                bool $isOrg,
                                ITranslator $translator,
                                ServicePayment $servicePayment,
                                PaymentMachine $machine,
                                PersonFactory $personFactory,
                                PersonProvider $personProvider,
                                ServiceEventPersonAccommodation $serviceEventPersonAccommodation,
                                ServicePaymentAccommodation $servicePaymentAccommodation
    ) {
        parent::__construct();
        $this->translator = $translator;
        $this->event = $event;
        $this->machine = $machine;
        $this->servicePayment = $servicePayment;
        $this->isOrg = $isOrg;
        $this->personFactory = $personFactory;
        $this->personProvider = $personProvider;
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
        $this->servicePaymentAccommodation = $servicePaymentAccommodation;
    }

    /**
     * @param \FKSDB\ORM\Models\ModelPayment $modelPayment
     */
    public function setModel(ModelPayment $modelPayment) {
        $this->model = $modelPayment;
    }

    /**
     * @return FormControl
     * @throws \FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentFormEdit() {
        return $this->createForm(false);
    }

    /**
     * @return FormControl
     * @throws \FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentFormCreate() {
        return $this->createForm(true);
    }

    /**
     * @param bool $create
     * @return FormControl
     * @throws \FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException
     * @throws \Nette\Application\BadRequestException
     */
    private function createForm(bool $create) {
        $control = new FormControl();
        $form = $control->getForm();
        if ($this->isOrg) {
            $form->addComponent($this->personFactory->createPersonSelect(true, _('Person'), $this->personProvider), 'person_id');
        } else {
            $form->addHidden('person_id');
        }
        $currencyField = new CurrencyField();
        $currencyField->setRequired(_('Please select currency'));
        $form->addComponent($currencyField, 'currency');
        $form->addComponent(new PaymentSelectField($this->serviceEventPersonAccommodation, $this->event, !$create), 'payment_accommodation');
        $form->addSubmit('submit', $create ? _('Proceed to summary') : _('Save payment'));
        $form->onSuccess[] = function (Form $form) use ($create) {
            $this->handleSubmit($form, $create);
        };
        return $control;
    }

    /**
     * @param Form $form
     * @param bool $create
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\ForbiddenRequestException
     * @throws \Exception
     */
    private function handleSubmit(Form $form, bool $create) {
        $values = $form->getValues();
        if ($create) {
            $model = $this->machine->createNewModel([
                'person_id' => $values->person_id,
                'event_id' => $this->event->event_id,
            ], $this->servicePayment);

        } else {
            $model = $this->model;
        }
        $this->servicePayment->updateModel($model, [
            'currency' => $values->currency,
            'person_id' => $values->offsetExists('person_id') ? $values->person_id : $model->person_id,
        ]);
        $this->servicePayment->save($model);

        $connection = $this->servicePayment->getConnection();
        $connection->beginTransaction();

        try {
            $this->servicePaymentAccommodation->prepareAndUpdate($values->payment_accommodation, $model);
        } catch (DuplicateAccommodationPaymentException $exception) {
            $this->flashMessage($exception->getMessage(), \BasePresenter::FLASH_ERROR);
            $connection->rollBack();
            return;
        } catch (EmptyDataException $exception) {
            $this->flashMessage($exception->getMessage(), \BasePresenter::FLASH_ERROR);
            $connection->rollBack();
            return;
        }
        $connection->commit();

        $this->getPresenter()->flashMessage($create ? _('Payment has been created.') : _('Payment has been updated.'));
        $this->getPresenter()->redirect('detail', ['id' => $model->payment_id]);
    }

    /**
     * @throws \Nette\Application\BadRequestException
     */
    public function renderCreate() {
        /**
         * @var FormControl $control
         */
        $control = $this->getComponent('formCreate');
        /**
         * @var ModelLogin $login
         */
        $login = $this->getPresenter()->getUser()->getIdentity();
        $control->getForm()->setDefaults([
            'person_id' => $login->getPerson()->person_id,
        ]);
        $this->template->setTranslator($this->translator);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'SelectForm.create.latte');
        $this->template->render();
    }

    /**
     * @param \FKSDB\ORM\Models\ModelPayment $model
     * @throws \Nette\Application\BadRequestException
     */
    public function renderEdit(ModelPayment $model) {
        $this->model = $model;
        $values = $model->toArray();
        $values['payment_accommodation'] = $this->serializePaymentAccommodation();
        /**
         * @var FormControl $control
         */
        $control = $this->getComponent('formEdit');
        $control->getForm()->setDefaults($values);
        $this->template->setTranslator($this->translator);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'SelectForm.edit.latte');
        $this->template->render();
    }

    /**
     * @return false|string
     */
    private function serializePaymentAccommodation() {
        $query = $this->model->getRelatedPersonAccommodation();
        $items = [];
        foreach ($query as $row) {
            $items[$row->event_person_accommodation_id] = true;
        }
        return \json_encode($items);
    }

}
