<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelSchool;
use FKSDB\Models\ORM\Services\ServiceAddress;
use FKSDB\Models\ORM\Services\ServiceSchool;
use FKSDB\Models\Utils\FormUtils;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\NetteORM\Exceptions\ModelException;
use Nette\Forms\Form;

/**
 * @property ModelSchool|null $model
 */
class SchoolFormComponent extends AbstractEntityFormComponent
{

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

    protected function configureForm(Form $form): void
    {
        $schoolContainer = $this->schoolFactory->createContainer();
        $form->addComponent($schoolContainer, self::CONT_SCHOOL);

        $addressContainer = $this->addressFactory->createAddress(null, true, true);
        $form->addComponent($addressContainer, self::CONT_ADDRESS);
    }

    /**
     * @throws ModelException
     */
    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues();
        $addressData = FormUtils::emptyStrToNull($values[self::CONT_ADDRESS], true);
        $schoolData = FormUtils::emptyStrToNull($values[self::CONT_SCHOOL], true);

        $connection = $this->serviceSchool->explorer->getConnection();
        $connection->beginTransaction();
        if (isset($this->model)) {
            /* Address */
            $this->serviceAddress->updateModel($this->model->getAddress(), $addressData);
            /* School */
            $this->serviceSchool->updateModel($this->model, $schoolData);
        } else {
            /* Address */
            $address = $this->serviceAddress->createNewModel($addressData);
            /* School */
            $schoolData['address_id'] = $address->address_id;
            $this->serviceSchool->createNewModel($schoolData);
        }
        $connection->commit();

        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('School has been updated') : _('School has been created'),
            BasePresenter::FLASH_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }

    /**
     * @throws BadTypeException
     */
    protected function setDefaults(): void
    {
        if (isset($this->model)) {
            $this->getForm()->setDefaults([
                self::CONT_SCHOOL => $this->model->toArray(),
                self::CONT_ADDRESS => $this->model->getAddress() ? $this->model->getAddress()->toArray() : null,
            ]);
        }
    }
}
