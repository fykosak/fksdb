<?php

namespace OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\DateTimeBox;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Grids\EventAccommodationGrid;
use FKSDB\Components\Grids\EventBilletedPerson;
use FKSDB\ORM\ModelEvent;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use ORM\IModel;
use ServiceEvent;
use ServiceEventAccommodation;

class EventAccommodationPresenter extends EntityPresenter {

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
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * @var ModelEvent
     */
    private $modelEvent;

    /**
     * @persistent
     */
    public $eventId;
    /**
     * @persistent
     */
    public $eventAccommodationId;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var \ServiceAddress
     */
    private $serviceAddress;


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

    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    protected function createComponentCreateComponent($name) {
        $control = $this->createForm();
        $form = $control->getForm();

        $form->addSubmit('send', _('Vložit'));
        $form->onSuccess[] = [$this, 'handleCreateFormSuccess'];

        return $control;
    }

    protected function createComponentEditComponent($name) {
        $control = $this->createForm();
        $form = $control->getForm();
        $form->addSubmit('send', _('Uložit'));
        $form->onSuccess[] = [$this, 'handleEditFormSuccess'];

        return $control;
    }

    protected function setDefaults(IModel $model = null, Form $form) {
        if (!$model) {
            return;
        }
        /**
         * @var $model \ModelEventAccommodation
         */
        $defaults = [
            self::CONT_ACCOMMODATION => $model->toArray(),
            self::CONT_ADDRESS => $model->getAddress() ? $model->getAddress()->toArray() : [],
        ];
        $form->setDefaults($defaults);
    }

    public function titleEdit() {
        /**
         * @var $model \ModelEventAccommodation
         */
        $model = $this->getModel();
        $this->setTitle(sprintf(
            _('Úprava ubytovaní v hoteli "%s" v dni %s akce "%s"'),
            $model->name,
            $model->date->format('Y-m-d'),
            $model->getEvent()->name
        ));
        $this->setIcon('fa fa-pencil');
    }

    public function titleCreate() {
        $this->setTitle(sprintf(_('Založit ubytovaní akce "%s"'), $this->getEvent()->name));
        $this->setIcon('fa fa-plus');
    }

    public function titleList() {
        $this->setTitle(sprintf(_('Ubytovaní akce "%s"'), $this->getEvent()->name));
        $this->setIcon('fa fa-table');
    }

    public function titleBilleted() {
        /**
         * @var $model \ModelEventAccommodation
         */
        $model = $this->serviceEventAccommodation->findByPrimary($this->eventAccommodationId);
        $this->setTitle(
            sprintf(_('List of accommodated people of hostel "%s" at %s of event "%s"'),
                $model->name,
                $model->date->format('Y-m-d'),
                $model->getEvent()->name
            ));
        $this->setIcon('fa fa-users');
    }

    private function createAccommodationContainer() {
        $container = new ModelContainer();

        $container->addText('name', _('Name'))->setRequired(true);
        $container->addText('capacity', _('Capacity'))->setRequired(true);

        $container->addText('price_kc', _('Price Kč'));

        $container->addText('price_eur', _('Price €'));
        $container->addComponent(new DateTimeBox(_('Date')), 'date');
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

    public function loadModel($id) {
        return $this->serviceEventAccommodation->findByPrimary($id);
    }

    /**
     * @return \AbstractModelSingle|ModelEvent
     * @throws BadRequestException
     */
    private function getEvent() {
        if (!$this->modelEvent) {
            $this->modelEvent = $this->serviceEvent->findByPrimary($this->eventId);
        }
        if (!$this->modelEvent) {
            throw new BadRequestException('No event!');
        }
        return $this->modelEvent;
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
             * @var $accommodation \ModelEventAccommodation
             * @var $address \ModelAddress
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
             * @var $accommodation \ModelEventAccommodation
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

    protected function createComponentGrid($name) {
        return new EventAccommodationGrid($this->eventId, $this->serviceEventAccommodation);
    }

    protected function createComponentBilletedGrid() {
        return new EventBilletedPerson($this->eventAccommodationId, $this->serviceEventPersonAccommodation);
    }
}
