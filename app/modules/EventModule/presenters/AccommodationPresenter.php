<?php

namespace EventModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\DateInput;
use FKSDB\Components\Forms\Controls\DateTimeBox;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Grids\EventAccommodationGrid;
use FKSDB\Components\Grids\EventBilletedPerson;
use FKSDB\ORM\ModelEventAccommodation;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use ServiceEventAccommodation;

class AccommodationPresenter extends BasePresenter {

    const CONT_ACCOMMODATION = 'CONT_ACCOMMODATION';
    const CONT_ADDRESS = 'CONT_ADDRESS';

    protected $modelResourceId = 'eventAccommodation';

    /**
     * @var ServiceEventAccommodation
     */
    private $serviceEventAccommodation;

    /**
     * @var \ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;
    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var \ServiceAddress
     */
    private $serviceAddress;
    /**
     * @var int
     * @persistent
     */
    public $id;


    public function injectAddressFactory(AddressFactory $addressFactory) {
        $this->addressFactory = $addressFactory;
    }

    public function injectServiceAddress(\ServiceAddress $serviceAddress) {
        $this->serviceAddress = $serviceAddress;
    }

    public function injectServiceEventAccommodation(ServiceEventAccommodation $serviceEventAccommodation) {
        $this->serviceEventAccommodation = $serviceEventAccommodation;
    }

    public function injectServiceEventPersonAccommodation(\ServiceEventPersonAccommodation $serviceEventPersonAccommodation) {
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
    }

    protected function createComponentCreateForm() {
        $control = $this->createForm();
        $form = $control->getForm();

        $form->addSubmit('send', _('Vložit'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleCreateFormSuccess($form);
        };
        return $control;
    }

    protected function createComponentEditForm() {
        $control = $this->createForm();
        $form = $control->getForm();
        $form->addSubmit('send', _('Uložit'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleEditFormSuccess($form);
        };

        return $control;
    }

    public function actionEdit() {
        $this['editForm']->getForm()->setDefaults($this->getDefaults());
    }

    protected function getDefaults() {
        $model = $this->getModel();
        if (!$model) {
            return null;
        }
        /**
         * @var $model \FKSDB\ORM\ModelEventAccommodation
         */
        return [
            self::CONT_ACCOMMODATION => $model->toArray(),
            self::CONT_ADDRESS => $model->getAddress() ? $model->getAddress()->toArray() : [],
        ];

    }

    public function titleEdit() {
        /**
         * @var $model \FKSDB\ORM\ModelEventAccommodation
         */
        $model = $this->getModel();
        $this->setTitle(sprintf(
            _('Úprava ubytovaní v hoteli "%s" v dni %s'),
            $model->name,
            $model->date->format('Y-m-d')
        ));
        $this->setIcon('fa fa-pencil');
    }

    public function titleCreate() {
        $this->setTitle(sprintf(_('Založit ubytovaní')));
        $this->setIcon('fa fa-plus');
    }

    public function titleList() {
        $this->setTitle(sprintf(_('Ubytovaní')));
        $this->setIcon('fa fa-table');
    }

    public function titleBilleted() {
        /**
         * @var $model \FKSDB\ORM\ModelEventAccommodation
         */
        $model = $this->serviceEventAccommodation->findByPrimary($this->id);
        $this->setTitle(
            sprintf(_('List of accommodated people of hostel "%s" at %s'),
                $model->name,
                $model->date->format('Y-m-d')
            ));
        $this->setIcon('fa fa-users');
    }

    private function createAccommodationContainer() {
        $container = new ModelContainer();

        $container->addText('name', _('Name'))->setRequired(true);
        $container->addText('capacity', _('Capacity'))->addRule(Form::INTEGER)->setRequired(true);

        $container->addText('price_kc', _('Price Kč'))->addRule(Form::FLOAT, _('Cena by mala byť číslo'));

        $container->addText('price_eur', _('Price €'))->addRule(Form::FLOAT, _('Cena by mala byť číslo'));
        $container->addComponent(new DateInput(_('Date')), 'date');
        return $container;
    }

    public function createForm() {
        $control = new FormControl();
        $form = $control->getForm();

        $schoolContainer = $this->createAccommodationContainer();
        $form->addComponent($schoolContainer, self::CONT_ACCOMMODATION);

        $addressContainer = $this->addressFactory->createAddress(AddressFactory::REQUIRED | AddressFactory::NOT_WRITEONLY);
        $form->addComponent($addressContainer, self::CONT_ADDRESS);

        return $control;
    }

    /**
     * @return ModelEventAccommodation
     */
    public function getModel() {
        $row = $this->serviceEventAccommodation->findByPrimary($this->id);
        return ModelEventAccommodation::createFromTableRow($row);

    }

    /**
     * @param Form $form
     * @throws \Nette\Application\AbortException
     */
    public function handleCreateFormSuccess(Form $form) {
        $connection = $this->serviceEventAccommodation->getConnection();
        $values = $form->getValues();


        try {
            if (!$connection->beginTransaction()) {
                throw new \ModelException();
            }
            /*
             * Address
             */
            $data = $this->getAddressFormData($values);
            $address = $this->serviceAddress->createNew($data);
            $this->serviceAddress->save($address);

            /*
             * Accommodation
             */
            $data = $this->getAccommodationFormData($values);
            /**
             * @var $accommodation \FKSDB\ORM\ModelEventAccommodation
             * @var $address \FKSDB\ORM\ModelAddress
             */
            $accommodation = $this->serviceEventAccommodation->createNew($data);
            $accommodation->event_id = $this->eventId;
            $accommodation->address_id = $address->address_id;
            $this->serviceEventAccommodation->save($accommodation);

            /*
             * Finalize
             */
            if (!$connection->commit()) {
                throw new \ModelException();
            }

            $this->flashMessage(_('Ubytovaní založeno'), self::FLASH_SUCCESS);
            $this->backlinkRedirect();
            $this->redirect('list'); // if there's no backlink
        } catch (\ModelException $e) {
            $connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage(_('Chyba při zakládání ubytovani.'), self::FLASH_ERROR);
        }
    }

    private function getAccommodationFormData($values) {
        return \FormUtils::emptyStrToNull($values[self::CONT_ACCOMMODATION]);
    }

    private function getAddressFormData($values) {
        return \FormUtils::emptyStrToNull($values[self::CONT_ADDRESS]);
    }

    /**
     * @param Form $form
     * @throws \Nette\Application\AbortException
     */
    public function handleEditFormSuccess(Form $form) {
        $connection = $this->serviceEventAccommodation->getConnection();
        $values = $form->getValues();


        try {
            if (!$connection->beginTransaction()) {
                throw new \ModelException();
            }
            /**
             * @var $accommodation \FKSDB\ORM\ModelEventAccommodation
             */
            $accommodation = $this->getModel();

            /*
             * Address
             */
            $data = $this->getAddressFormData($values);
            $address = $accommodation->getAddress();
            if (!$address) {
                $address = $this->serviceAddress->createNew($data);
            } else {
                $this->serviceAddress->updateModel($address, $data);
            }
            $this->serviceAddress->save($address);

            /*
             * Accommodation
             */
            $data = $this->getAccommodationFormData($values);
            $this->serviceEventAccommodation->updateModel($accommodation, $data);
            $this->serviceEventAccommodation->save($accommodation);

            /*
             * Finalize
             */
            if (!$connection->commit()) {
                throw new \ModelException();
            }

            $this->flashMessage(_('Ubytovaní upraveno'), self::FLASH_SUCCESS);
            $this->backlinkRedirect();
            $this->redirect('list'); // if there's no backlink
        } catch (\ModelException $e) {
            $connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage(_('Chyba při ubytovaní.'), self::FLASH_ERROR);
        }
    }

    protected function createComponentGrid() {
        return new EventAccommodationGrid($this->getEventId(), $this->serviceEventAccommodation);
    }

    protected function createComponentBilletedGrid() {
        return new EventBilletedPerson($this->id, $this->serviceEventPersonAccommodation);
    }
}
