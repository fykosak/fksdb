<?php

namespace EventModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\DateInput;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Grids\EventAccommodationGrid;
use FKSDB\Components\Grids\EventBilletedPerson;
use FKSDB\ORM\ModelEventAccommodation;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use ServiceEventAccommodation;

class AccommodationPresenter extends BasePresenter {

    const CONT_ACCOMMODATION = 'CONT_ACCOMMODATION';
    const CONT_ADDRESS = 'CONT_ADDRESS';

    protected $modelResourceId = 'event.accommodation';

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

    /**
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function authorizedList() {
        return $this->setAuthorized($this->isContestsOrgAllowed('event.accommodation', 'list'));
    }

    /**
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function authorizedCreate() {
        return $this->setAuthorized($this->isContestsOrgAllowed('event.accommodation', 'create'));
    }

    /**
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function authorizedEdit() {
        return $this->setAuthorized($this->isContestsOrgAllowed('event.accommodation', 'edit'));
    }

    /**
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function authorizedBilleted() {
        return $this->setAuthorized($this->isContestsOrgAllowed('event.accommodation', 'billeted'));
    }

    /**
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    public function titleEdit() {
        $model = $this->getModel();
        $this->setTitle(sprintf(
            _('Úprava ubytovaní v hoteli "%s" v dni %s'),
            $model->name,
            $model->date->format('d. m. Y')
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

    /**
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    public function titleBilleted() {
        $model = $this->getModel();
        $this->setTitle(
            sprintf(_('List of accommodated people of hostel "%s" at %s'),
                $model->name,
                $model->date->format('d. m. Y')
            ));
        $this->setIcon('fa fa-users');
    }

    /**
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    public function actionEdit() {
        $this->getComponent('editForm')->getForm()->setDefaults($this->getDefaults());
    }

    /**
     * @return FormControl
     */
    public function createComponentCreateForm(): FormControl {
        $control = $this->createForm();
        $form = $control->getForm();

        $form->addSubmit('send', _('Vložit'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleCreateFormSuccess($form);
        };
        return $control;
    }

    /**
     * @return FormControl
     */
    public function createComponentEditForm(): FormControl {
        $control = $this->createForm();
        $form = $control->getForm();
        $form->addSubmit('send', _('Uložit'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleEditFormSuccess($form);
        };

        return $control;
    }

    /**
     * @return EventAccommodationGrid
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function createComponentGrid(): EventAccommodationGrid {
        return new EventAccommodationGrid($this->getEvent(), $this->serviceEventAccommodation);
    }

    /**
     * @return EventBilletedPerson
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    public function createComponentBilletedGrid(): EventBilletedPerson {
        return new EventBilletedPerson($this->getModel(), $this->serviceEventPersonAccommodation);
    }

    /**
     * @return ModelContainer
     */
    private function createAccommodationContainer(): ModelContainer {
        $container = new ModelContainer();

        $container->addText('name', _('Name'))->setRequired(true);
        $container->addText('capacity', _('Capacity'))->addRule(Form::INTEGER)->setRequired(true);

        $container->addText('price_kc', _('Price Kč'))->addRule(Form::FLOAT, _('Cena by mala byť číslo'));

        $container->addText('price_eur', _('Price €'))->addRule(Form::FLOAT, _('Cena by mala byť číslo'));
        $container->addComponent(new DateInput(_('Date')), 'date');
        return $container;
    }

    /**
     * @return FormControl
     */
    private function createForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();

        $schoolContainer = $this->createAccommodationContainer();
        $form->addComponent($schoolContainer, self::CONT_ACCOMMODATION);

        $addressContainer = $this->addressFactory->createAddress(AddressFactory::REQUIRED | AddressFactory::NOT_WRITEONLY);
        $form->addComponent($addressContainer, self::CONT_ADDRESS);

        return $control;
    }

    /**
     * @return array|null
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    private function getDefaults() {
        $model = $this->getModel();
        if (!$model) {
            return null;
        }
        return [
            self::CONT_ACCOMMODATION => $model->toArray(),
            self::CONT_ADDRESS => $model->getAddress() ? $model->getAddress()->toArray() : [],
        ];
    }

    /**
     * @return ModelEventAccommodation
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    public function getModel(): ModelEventAccommodation {
        $row = $this->serviceEventAccommodation->findByPrimary($this->id);
        if (!$row) {
            throw new BadRequestException(_('Accommodation does not exists'));
        }
        $model = ModelEventAccommodation::createFromTableRow($row);
        if ($this->getEventId() !== $model->event_id) {
            throw new ForbiddenRequestException(_('Ubytovanie nepatrí k tomuto eventu'));
        }
        return $model;

    }

    /**
     * @param Form $form
     * @throws \Nette\Application\AbortException
     */
    private function handleCreateFormSuccess(Form $form) {
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

    /**
     * @param $values
     * @return array
     */
    private function getAccommodationFormData($values) {
        return \FormUtils::emptyStrToNull($values[self::CONT_ACCOMMODATION]);
    }

    /**
     * @param $values
     * @return array
     */
    private function getAddressFormData($values) {
        return \FormUtils::emptyStrToNull($values[self::CONT_ADDRESS]);
    }

    /**
     * @param Form $form
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    private function handleEditFormSuccess(Form $form) {
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

}
