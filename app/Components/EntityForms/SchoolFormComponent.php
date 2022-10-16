<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\AddressService;
use FKSDB\Models\ORM\Services\SchoolService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\Message;
use Nette\Forms\Form;

/**
 * @property SchoolModel|null $model
 */
class SchoolFormComponent extends EntityFormComponent
{
    public const CONT_ADDRESS = 'address';
    public const CONT_SCHOOL = 'school';

    private AddressService $addressService;
    private SchoolService $schoolService;
    private SchoolFactory $schoolFactory;
    private AddressFactory $addressFactory;

    final public function injectPrimary(
        AddressFactory $addressFactory,
        SchoolFactory $schoolFactory,
        AddressService $addressService,
        SchoolService $schoolService
    ): void {
        $this->addressFactory = $addressFactory;
        $this->schoolFactory = $schoolFactory;
        $this->addressService = $addressService;
        $this->schoolService = $schoolService;
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
        $addressData = FormUtils::emptyStrToNull2($values[self::CONT_ADDRESS]);
        $schoolData = FormUtils::emptyStrToNull2($values[self::CONT_SCHOOL]);

        $connection = $this->schoolService->explorer->getConnection();
        $connection->beginTransaction();
        if (isset($this->model)) {
            /* Address */
            $this->addressService->storeModel($addressData, $this->model->address);
            /* School */
            $this->schoolService->storeModel($schoolData, $this->model);
        } else {
            /* Address */
            $address = $this->addressService->storeModel($addressData);
            /* School */
            $schoolData['address_id'] = $address->address_id;
            $this->schoolService->storeModel($schoolData);
        }
        $connection->commit();

        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('School has been updated') : _('School has been created'),
            Message::LVL_SUCCESS
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
                self::CONT_ADDRESS => $this->model->address ? $this->model->address->toArray() : null,
            ]);
        }
    }
}
