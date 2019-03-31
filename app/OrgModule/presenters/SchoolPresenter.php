<?php

namespace OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Components\Grids\SchoolsGrid;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Services\ServiceAddress;
use FKSDB\ORM\Services\ServiceSchool;
use FormUtils;
use ModelException;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Nette\NotImplementedException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SchoolPresenter extends EntityPresenter {

    const CONT_ADDRESS = 'address';
    const CONT_SCHOOL = 'school';

    protected $modelResourceId = 'school';

    /**
     * @var \FKSDB\ORM\Services\ServiceSchool
     */
    private $serviceSchool;

    /**
     * @var \FKSDB\ORM\Services\ServiceAddress
     */
    private $serviceAddress;

    /**
     * @var SchoolFactory
     */
    private $schoolFactory;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @param \FKSDB\ORM\Services\ServiceSchool $serviceSchool
     */
    public function injectServiceSchool(ServiceSchool $serviceSchool) {
        $this->serviceSchool = $serviceSchool;
    }

    /**
     * @param ServiceAddress $serviceAddress
     */
    public function injectServiceAddress(ServiceAddress $serviceAddress) {
        $this->serviceAddress = $serviceAddress;
    }

    /**
     * @param SchoolFactory $schoolFactory
     */
    public function injectSchoolFactory(SchoolFactory $schoolFactory) {
        $this->schoolFactory = $schoolFactory;
    }

    /**
     * @param AddressFactory $addressFactory
     */
    public function injectAddressFactory(AddressFactory $addressFactory) {
        $this->addressFactory = $addressFactory;
    }

    public function titleList() {
        $this->setTitle(_('Školy'));
        $this->setIcon('fa fa-university');
    }

    public function titleCreate() {
        $this->setTitle(_('Založit školu'));
        $this->setIcon('fa fa-plus');
    }

    public function titleEdit() {
        $school = $this->getModel();
        $this->setTitle(sprintf(_('Úprava školy %s'), $school->name_abbrev));
        $this->setIcon('fa fa-pencil');
    }

    public function actionDelete() {
        // This should set active flag to false.
        throw new NotImplementedException(null, 501);
    }

    /**
     * @param $name
     * @return FormControl
     * @throws \Nette\Application\BadRequestException
     */
    protected function createComponentCreateComponent($name) {
        $control = $this->createForm();
        $form = $control->getForm();

        $form->addSubmit('send', _('Vložit'));
        $form->onSuccess[] = [$this, 'handleCreateFormSuccess'];

        return $control;
    }

    /**
     * @param $name
     * @return FormControl
     * @throws \Nette\Application\BadRequestException
     */
    protected function createComponentEditComponent($name) {
        $control = $this->createForm();
        $form = $control->getForm();
        $form->addSubmit('send', _('Uložit'));
        $form->onSuccess[] = [$this, 'handleEditFormSuccess'];

        return $control;
    }

    /**
     * @param \FKSDB\ORM\IModel|null $model
     * @param Form $form
     */
    protected function setDefaults(IModel $model = null, Form $form) {
        if (!$model) {
            return;
        }
        /**
         * @var \FKSDB\ORM\Models\ModelEventAccommodation $model
         */
        $defaults = [
            self::CONT_SCHOOL => $model->toArray(),
            self::CONT_ADDRESS => $model->getAddress() ? $model->getAddress()->toArray() : null,
        ];

        $form->setDefaults($defaults);
    }

    /**
     * @param $name
     * @return SchoolsGrid
     */
    protected function createComponentGrid($name) {
        return new SchoolsGrid($this->serviceSchool);
    }

    /**
     * @return FormControl
     * @throws \Nette\Application\BadRequestException
     */
    private function createForm() {
        $control = new FormControl();
        $form = $control->getForm();
        $schoolContainer = $this->schoolFactory->createSchool();
        $form->addComponent($schoolContainer, self::CONT_SCHOOL);

        $addressContainer = $this->addressFactory->createAddress(AddressFactory::REQUIRED | AddressFactory::NOT_WRITEONLY);
        $form->addComponent($addressContainer, self::CONT_ADDRESS);

        return $control;
    }

    /**
     * @param $id
     * @return \FKSDB\ORM\AbstractModelSingle|\Nette\Database\Table\ActiveRow|null
     */
    protected function loadModel($id) {
        return $this->serviceSchool->findByPrimary($id);
    }

    /**
     * @internal
     * @param Form $form
     * @throws \Nette\Application\AbortException
     * @throws \ReflectionException
     */
    public function handleCreateFormSuccess(Form $form) {
        $connection = $this->serviceSchool->getConnection();
        $values = $form->getValues();


        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }

            /*
             * Address
             */
            $data = FormUtils::emptyStrToNull($values[self::CONT_ADDRESS]);
            $address = $this->serviceAddress->createNew($data);
            $this->serviceAddress->save($address);

            /*
             * School
             */
            $data = FormUtils::emptyStrToNull($values[self::CONT_SCHOOL]);
            $school = $this->serviceSchool->createNew($data);
            $school->address_id = $address->address_id;
            $this->serviceSchool->save($school);

            /*
             * Finalize
             */
            if (!$connection->commit()) {
                throw new ModelException();
            }

            $this->flashMessage(_('Škola založena'), self::FLASH_SUCCESS);
            $this->backLinkRedirect();
            $this->redirect('list'); // if there's no backlink
        } catch (ModelException $exception) {
            $connection->rollBack();
            Debugger::log($exception, Debugger::ERROR);
            $this->flashMessage(_('Chyba při zakládání školy.'), self::FLASH_ERROR);
        }
    }

    /**
     * @internal
     * @param Form $form
     * @throws \Nette\Application\AbortException
     * @throws \ReflectionException
     */
    public function handleEditFormSuccess(Form $form) {
        $connection = $this->serviceSchool->getConnection();
        $values = $form->getValues();
        $school = $this->getModel();
        $address = $school->getAddress();

        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }

            /*
             * Address
             */
            $data = FormUtils::emptyStrToNull($values[self::CONT_ADDRESS]);
            $this->serviceAddress->updateModel($address, $data);
            $this->serviceAddress->save($address);

            /*
             * School
             */
            $data = FormUtils::emptyStrToNull($values[self::CONT_SCHOOL]);
            $this->serviceSchool->updateModel($school, $data);
            $this->serviceSchool->save($school);

            /*
             * Finalize
             */
            if (!$connection->commit()) {
                throw new ModelException();
            }

            $this->flashMessage(_('Škola upravena'), self::FLASH_SUCCESS);
            $this->backLinkRedirect();
            $this->redirect('list'); // if there's no backlink
        } catch (ModelException $exception) {
            $connection->rollBack();
            Debugger::log($exception, Debugger::ERROR);
            $this->flashMessage(_('Chyba při úpravě školy.'), self::FLASH_ERROR);
        }
    }

}
