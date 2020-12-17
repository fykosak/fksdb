<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Model\ORM\Models\ModelSchool;
use FKSDB\Model\ORM\Services\ServiceAddress;
use FKSDB\Model\ORM\Services\ServiceSchool;
use FKSDB\Model\Utils\FormUtils;
use Nette\Application\AbortException;
use Nette\Forms\Form;

/**
 * Class AbstractForm
 * @author Michal Červeňák <miso@fykos.cz>
 * @property ModelSchool $model
 */
class SchoolFormComponent extends AbstractEntityFormComponent {

    public const CONT_ADDRESS = 'address';
    public const CONT_SCHOOL = 'school';

    private ServiceAddress $serviceAddress;
    private ServiceSchool $serviceSchool;
    private SchoolFactory $schoolFactory;
    private AddressFactory $addressFactory;

    final public function injectPrimary(
        AddressFactory $addressFactory,
        SchoolFactory $schoolFactory,
        ServiceAddress $serviceAddress,
        ServiceSchool $serviceSchool
    ): void {
        $this->addressFactory = $addressFactory;
        $this->schoolFactory = $schoolFactory;
        $this->serviceAddress = $serviceAddress;
        $this->serviceSchool = $serviceSchool;
    }

    protected function configureForm(Form $form): void {
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
    protected function handleFormSuccess(Form $form): void {
        $values = $form->getValues();
        $addressData = FormUtils::emptyStrToNull($values[self::CONT_ADDRESS], true);
        $schoolData = FormUtils::emptyStrToNull($values[self::CONT_SCHOOL], true);

        $connection = $this->serviceSchool->getConnection();
        $connection->beginTransaction();
        if (isset($this->model)) {
            /* Address */
            $this->serviceAddress->updateModel2($this->model->getAddress(), $addressData);
            /* School */
            $this->serviceSchool->updateModel2($this->model, $schoolData);
        } else {
            /* Address */
            $address = $this->serviceAddress->createNewModel($addressData);
            /* School */
            $schoolData['address_id'] = $address->address_id;
            $this->serviceSchool->createNewModel($schoolData);
        }
        $connection->commit();

        $this->getPresenter()->flashMessage(!isset($this->model) ? _('School has been created') : _('School has been updated'), BasePresenter::FLASH_SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    /**
     * @return void
     * @throws BadTypeException
     */
    protected function setDefaults(): void {
        if (isset($this->model)) {
            $this->getForm()->setDefaults([
                self::CONT_SCHOOL => $this->model->toArray(),
                self::CONT_ADDRESS => $this->model->getAddress() ? $this->model->getAddress()->toArray() : null,
            ]);
        }
    }
}
