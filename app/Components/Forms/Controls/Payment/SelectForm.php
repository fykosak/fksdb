<?php

namespace FKSDB\Components\Forms\Controls\Payment;

use BasePresenter;
use Exception;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\ORM\Services\Schedule\ServiceSchedulePayment;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\Handler\DuplicatePaymentException;
use FKSDB\Payment\Handler\EmptyDataException;
use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;
use FKSDB\Payment\Transition\PaymentMachine;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;
use Nette\Utils\JsonException;
use function json_encode;

/**
 * Class SelectForm
 * @package FKSDB\Components\Forms\Factories\Payment
 * @property FileTemplate $template
 */
class SelectForm extends Control {
    /**
     * @var string
     */
    private $type;
    /**
     * @var PersonFactory
     */
    private $personFactory;
    /**
     * @var PersonProvider
     */
    private $personProvider;
    /**
     * @var ServicePersonSchedule
     */
    private $servicePersonSchedule;
    /**
     * @var ModelEvent
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
     * @var ModelPayment
     */
    private $model;
    /**
     * @var ServicePayment
     */
    private $servicePayment;
    /**
     * @var ServiceSchedulePayment
     */
    private $serviceSchedulePayment;

    /**
     * SelectForm constructor.
     * @param ModelEvent $event
     * @param bool $isOrg
     * @param string $type
     * @param ITranslator $translator
     * @param ServicePayment $servicePayment
     * @param PaymentMachine $machine
     * @param PersonFactory $personFactory
     * @param PersonProvider $personProvider
     * @param ServicePersonSchedule $servicePersonSchedule
     * @param ServiceSchedulePayment $serviceSchedulePayment
     */
    public function __construct(ModelEvent $event,
                                bool $isOrg,
                                string $type,
                                ITranslator $translator,
                                ServicePayment $servicePayment,
                                PaymentMachine $machine,
                                PersonFactory $personFactory,
                                PersonProvider $personProvider,
                                ServicePersonSchedule $servicePersonSchedule,
                                ServiceSchedulePayment $serviceSchedulePayment
    ) {
        parent::__construct();
        $this->translator = $translator;
        $this->event = $event;
        $this->machine = $machine;
        $this->servicePayment = $servicePayment;
        $this->isOrg = $isOrg;
        $this->type = $type;
        $this->personFactory = $personFactory;
        $this->personProvider = $personProvider;
        $this->servicePersonSchedule = $servicePersonSchedule;
        $this->serviceSchedulePayment = $serviceSchedulePayment;
    }

    /**
     * @param ModelPayment $modelPayment
     */
    public function setModel(ModelPayment $modelPayment) {
        $this->model = $modelPayment;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws UnsupportedCurrencyException
     * @throws JsonException
     */
    public function createComponentFormEdit() {
        return $this->createForm(false);
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws UnsupportedCurrencyException
     * @throws JsonException
     */
    public function createComponentFormCreate() {
        return $this->createForm(true);
    }

    /**
     * @param bool $create
     * @return FormControl
     * @throws UnsupportedCurrencyException
     * @throws BadRequestException
     * @throws JsonException
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
        $form->addComponent(new PaymentSelectField($this->servicePersonSchedule, $this->event, [$this->type], !$create), 'payment_accommodation');
        $form->addSubmit('submit', $create ? _('Proceed to summary') : _('Save payment'));
        $form->onSuccess[] = function (Form $form) use ($create) {
            $this->handleSubmit($form, $create);
        };
        return $control;
    }

    /**
     * @param Form $form
     * @param bool $create
     * @throws AbortException
     * @throws ForbiddenRequestException
     * @throws Exception
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
        $model->update([
                'currency' => $values->currency,
                'person_id' => $values->offsetExists('person_id') ? $values->person_id : $model->person_id,
            ]
        );

        $connection = $this->servicePayment->getConnection();
        $connection->beginTransaction();

        try {
            $this->serviceSchedulePayment->prepareAndUpdate($values->payment_accommodation, $model);
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

        $this->getPresenter()->flashMessage($create ? _('Payment has been created.') : _('Payment has been updated.'));
        $this->getPresenter()->redirect('detail', ['id' => $model->payment_id]);
    }

    /**
     * @throws BadRequestException
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
     * @param ModelPayment $model
     * @throws BadRequestException
     */
    public function renderEdit(ModelPayment $model) {
        $this->model = $model;
        $values = $model->toArray();
        $values['payment_accommodation'] = $this->serializeScheduleValue();
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
     * @return string
     */
    private function serializeScheduleValue() {
        $query = $this->model->getRelatedPersonSchedule();
        $items = [];
        foreach ($query as $row) {
            $items[$row->person_schedule_id] = true;
        }
        return json_encode($items);
    }
}
