<?php

namespace CommonModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Components\Grids\ContestantsFromSchoolGrid;
use FKSDB\Components\Grids\SchoolsGrid;
use FKSDB\EntityTrait;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IService;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Services\ServiceAddress;
use FKSDB\ORM\Services\ServiceSchool;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Tracy\Debugger;

/**
 * Class SchoolPresenter
 * @package CommonModule
 * @method ModelSchool getEntity()
 * @method ModelSchool loadEntity(int $id)
 */
class SchoolPresenter extends BasePresenter {
    use EntityTrait;

    const CONT_ADDRESS = 'address';
    const CONT_SCHOOL = 'school';

    /** @var ServiceAddress */
    private $serviceAddress;

    /** @var ServiceSchool */
    private $serviceSchool;

    /** @var SchoolFactory */
    private $schoolFactory;

    /** @var AddressFactory */
    private $addressFactory;

    /** @param ServiceSchool $serviceSchool */
    public function injectServiceSchool(ServiceSchool $serviceSchool) {
        $this->serviceSchool = $serviceSchool;
    }

    /** @param ServiceAddress $serviceAddress */
    public function injectServiceAddress(ServiceAddress $serviceAddress) {
        $this->serviceAddress = $serviceAddress;
    }

    /** @param SchoolFactory $schoolFactory */
    public function injectSchoolFactory(SchoolFactory $schoolFactory) {
        $this->schoolFactory = $schoolFactory;
    }

    /**  @param AddressFactory $addressFactory */
    public function injectAddressFactory(AddressFactory $addressFactory) {
        $this->addressFactory = $addressFactory;
    }

    public function titleList() {
        $this->setTitle(_('Schools'));
        $this->setIcon('fa fa-university');
    }

    public function titleCreate() {
        $this->setTitle(_('Založit školu'));
        $this->setIcon('fa fa-plus');
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function titleEdit(int $id) {
        $this->setTitle(sprintf(_('Úprava školy %s'), $this->loadEntity($id)->name_abbrev));
        $this->setIcon('fa fa-pencil');
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function titleDetail(int $id) {
        $this->setTitle(sprintf(_('Detail of school %s'), $this->loadEntity($id)->name_abbrev));
        $this->setIcon('fa fa-university');
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function actionDetail(int $id) {
        $this->loadEntity($id);
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function actionEdit(int $id) {
        $this->traitActionEdit($id);
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function renderDetail(int $id) {
        $this->template->model = $this->loadEntity($id);
    }

    /**
     * @return IService|ServiceSchool
     */
    protected function getORMService() {
        return $this->serviceSchool;
    }

    /**
     * @return SchoolsGrid
     */
    protected function createComponentGrid(): SchoolsGrid {
        return new SchoolsGrid($this->serviceSchool);
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function getCreateForm(): FormControl {
        $control = $this->createForm();
        $form = $control->getForm();

        $form->addSubmit('send', _('Create'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleCreateFormSuccess($form);
        };

        return $control;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function getEditForm(): FormControl {
        $control = $this->createForm();
        $form = $control->getForm();
        $form->addSubmit('send', _('Save'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleEditFormSuccess($form);
        };

        return $control;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
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
     * @param Form $form
     * @throws AbortException
     * @throws \ReflectionException
     */
    public function handleCreateFormSuccess(Form $form) {
        $connection = $this->serviceSchool->getConnection();
        $values = $form->getValues();

        try {
            if (!$connection->beginTransaction()) {
                throw new \ModelException();
            }

            /* Address */
            $data = \FormUtils::emptyStrToNull($values[self::CONT_ADDRESS]);
            $address = $this->getORMService()->createNewModel($data);
            /* School */
            $data = \FormUtils::emptyStrToNull($values[self::CONT_SCHOOL]);
            $data['address_id'] = $address->address_id;
            $this->serviceSchool->createNewModel($data);
            /* Finalize */
            if (!$connection->commit()) {
                throw new \ModelException();
            }

            $this->flashMessage(_('Škola založena'), self::FLASH_SUCCESS);
            $this->backLinkRedirect();
            $this->redirect(':Common:school:list'); // if there's no backlink
        } catch (\ModelException $exception) {
            $connection->rollBack();
            Debugger::log($exception, Debugger::ERROR);
            $this->flashMessage(_('Chyba při zakládání školy.'), self::FLASH_ERROR);
        }
    }

    /**
     * @param Form $form
     * @throws AbortException
     * @throws \ReflectionException
     */
    public function handleEditFormSuccess(Form $form) {
        $connection = $this->serviceSchool->getConnection();
        $values = $form->getValues();
        $school = $this->getEntity();
        $address = $school->getAddress();

        try {
            if (!$connection->beginTransaction()) {
                throw new \ModelException();
            }

            /* Address */
            $data = \FormUtils::emptyStrToNull($values[self::CONT_ADDRESS]);
            $this->serviceAddress->updateModel2($address, $data);

            /* School */
            $data = \FormUtils::emptyStrToNull($values[self::CONT_SCHOOL]);
            $this->serviceSchool->updateModel2($school, $data);

            /* Finalize */
            if (!$connection->commit()) {
                throw new \ModelException();
            }

            $this->flashMessage(_('Škola upravena'), self::FLASH_SUCCESS);
            $this->backLinkRedirect();
            $this->redirect(':Common:School:list');
        } catch (\ModelException $exception) {
            $connection->rollBack();
            Debugger::log($exception, Debugger::ERROR);
            $this->flashMessage(_('Chyba při úpravě školy.'), self::FLASH_ERROR);
        }
    }

    /**
     * @param AbstractModelSingle|ModelSchool $model
     * @return array
     */
    protected function getFormDefaults(AbstractModelSingle $model): array {
        return [
            self::CONT_SCHOOL => $model->toArray(),
            self::CONT_ADDRESS => $model->getAddress() ? $model->getAddress()->toArray() : null,
        ];
    }

    /**
     * @return ContestantsFromSchoolGrid
     */
    protected function createComponentContestantsFromSchoolGrid(): ContestantsFromSchoolGrid {
        return new ContestantsFromSchoolGrid($this->getEntity(), $this->getORMService());
    }

    /**
     * @return string
     */
    protected function getModelResource(): string {
        return ModelSchool::RESOURCE_ID;
    }
}
