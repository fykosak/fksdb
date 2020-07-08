<?php

namespace FKSDB\Components\Controls\Entity\School;

use FKSDB\Components\Controls\Entity\AbstractEntityFormComponent;
use FKSDB\Components\Controls\Entity\IEditEntityForm;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\ModelException;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Services\ServiceAddress;
use FKSDB\ORM\Services\ServiceSchool;
use FKSDB\Utils\FormUtils;
use Nette\Application\AbortException;
use Nette\Forms\Form;
use Tracy\Debugger;

/**
 * Class AbstractForm
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SchoolFormComponent extends AbstractEntityFormComponent implements IEditEntityForm {

    const CONT_ADDRESS = 'address';
    const CONT_SCHOOL = 'school';

    /** @var ServiceAddress */
    protected $serviceAddress;

    /** @var ServiceSchool */
    protected $serviceSchool;

    /** @var SchoolFactory */
    protected $schoolFactory;

    /** @var AddressFactory */
    protected $addressFactory;

    /**
     * @var ModelSchool;
     */
    protected $model;

    /**
     * @param AddressFactory $addressFactory
     * @param SchoolFactory $schoolFactory
     * @param ServiceAddress $serviceAddress
     * @param ServiceSchool $serviceSchool
     * @return void
     */
    public function injectPrimary(
        AddressFactory $addressFactory,
        SchoolFactory $schoolFactory,
        ServiceAddress $serviceAddress,
        ServiceSchool $serviceSchool
    ) {
        $this->addressFactory = $addressFactory;
        $this->schoolFactory = $schoolFactory;
        $this->serviceAddress = $serviceAddress;
        $this->serviceSchool = $serviceSchool;
    }

    /**
     * @param Form $form
     * @return void
     */
    protected function configureForm(Form $form) {
        $schoolContainer = $this->schoolFactory->createContainer();
        $form->addComponent($schoolContainer, self::CONT_SCHOOL);

        $addressContainer = $this->addressFactory->createAddress(AddressFactory::REQUIRED | AddressFactory::NOT_WRITEONLY);
        $form->addComponent($addressContainer, self::CONT_ADDRESS);
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    protected function handleFormSuccess(Form $form) {
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull($values, true);
        $connection = $this->serviceSchool->getConnection();
        try {
            $connection->beginTransaction();
            if ($this->create) {
                $address = $this->model->getAddress();
                /* Address */
                $this->serviceAddress->updateModel2($address, $data[self::CONT_ADDRESS]);
                /* School */
                $this->serviceSchool->updateModel2($this->model, $data[self::CONT_SCHOOL]);
            } else {
                /* Address */
                $address = $this->serviceAddress->createNewModel($data[self::CONT_ADDRESS]);
                /* School */
                $data['address_id'] = $address->address_id;
                $this->serviceSchool->createNewModel($data[self::CONT_ADDRESS]);
            }
            $connection->commit();

            $this->getPresenter()->flashMessage($this->create ? _('School has been created') : _('School has been updated'), BasePresenter::FLASH_SUCCESS);
            $this->getPresenter()->redirect('list');
        } catch (ModelException $exception) {
            $connection->rollBack();
            Debugger::log($exception, Debugger::ERROR);
            $this->getPresenter()->flashMessage($this->create ? _('Chyba při zakládání školy.') : _('Chyba při úpravě školy.'), BasePresenter::FLASH_ERROR);
        }
    }

    /**
     * @param AbstractModelSingle|ModelSchool $model
     * @return void
     * @throws BadTypeException
     */
    public function setModel(AbstractModelSingle $model) {
        $this->model = $model;
        $this->getForm()->setDefaults([
            self::CONT_SCHOOL => $model->toArray(),
            self::CONT_ADDRESS => $model->getAddress() ? $model->getAddress()->toArray() : null,
        ]);
    }
}
