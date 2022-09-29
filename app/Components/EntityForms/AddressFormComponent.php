<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Referenced\Address\AddressHandler;
use FKSDB\Components\Forms\Referenced\Address\AddressSearchContainer;
use FKSDB\Components\Forms\Referenced\Address\AddressDataContainer;
use FKSDB\Components\Forms\Referenced\ReferencedId;
use FKSDB\Models\ORM\Models\AddressModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactModel;
use FKSDB\Models\ORM\Models\PostContactType;
use FKSDB\Models\ORM\Services\AddressService;
use FKSDB\Models\ORM\Services\PostContactService;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @property PostContactModel $model
 */
class AddressFormComponent extends EntityFormComponent
{
    private PostContactType $postContactType;
    private AddressService $addressService;
    private PostContactService $postContactService;
    private PersonModel $person;

    public function __construct(
        Container $container,
        PostContactType $postContactType,
        PersonModel $person
    ) {
        parent::__construct($container, $person->getPostContact($postContactType));
        $this->postContactType = $postContactType;
        $this->person = $person;
    }

    public function inject(AddressService $addressService, PostContactService $postContactService): void
    {
        $this->addressService = $addressService;
        $this->postContactService = $postContactService;
    }

    protected function configureForm(Form $form): void
    {
        $address = new ReferencedId(
            new AddressSearchContainer($this->container),
            new AddressDataContainer($this->container, false, true),
            $this->addressService,
            new AddressHandler($this->container)
        );
        $form->addComponent($address, 'address');
    }

    protected function handleFormSuccess(Form $form): void
    {
        $form->getValues('array');// trigger referencedId
        /** @var ReferencedId $referencedId */
        $referencedId = $form->getComponent('address');
        /** @var AddressModel $address */
        $address = $referencedId->getModel();
        $this->postContactService->storeModel(
            [
                'type' => $this->postContactType->value,
                'address_id' => $address->address_id,
                'person_id' => $this->person->person_id,
            ],
            $this->model
        );
    }

    protected function setDefaults(): void
    {
        $this->getForm()->setValues(
            ['address' => isset($this->model) ? $this->model->address_id : ReferencedId::VALUE_PROMISE]
        );
    }
}
