<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Services\ServiceAddress;
use FKSDB\ORM\Services\ServiceSchool;
use FKSDB\Utils\FormUtils;
use Nette\Application\AbortException;
use Nette\Forms\Form;

/**
 * Class AbstractForm
 * @author Michal Červeňák <miso@fykos.cz>
 * @property ModelSchool $model
 */
class SchoolFormComponent extends EditEntityFormComponent {

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
        if ($this->create) {
            /* Address */
            $address = $this->serviceAddress->createNewModel($addressData);
            /* School */
            $schoolData['address_id'] = $address->address_id;
            $this->serviceSchool->createNewModel($schoolData);
        } else {
            /* Address */
            $this->serviceAddress->updateModel2($this->model->getAddress(), $addressData);
            /* School */
            $this->serviceSchool->updateModel2($this->model, $schoolData);
        }
        $connection->commit();

        $this->getPresenter()->flashMessage($this->create ? _('School has been created') : _('School has been updated'), BasePresenter::FLASH_SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    /**
     * @param AbstractModelSingle|ModelSchool|null $model
     * @return void
     * @throws BadTypeException
     */
    protected function setDefaults(?AbstractModelSingle $model): void {
        if (!is_null($model)) {
            $this->getForm()->setDefaults([
                self::CONT_SCHOOL => $model->toArray(),
                self::CONT_ADDRESS => $model->getAddress() ? $model->getAddress()->toArray() : null,
            ]);
        }
    }
}
