<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Referenced\Address\AddressDataContainer;
use FKSDB\Components\Forms\Referenced\Address\AddressHandler;
use FKSDB\Components\Forms\Referenced\Address\AddressSearchContainer;
use FKSDB\Models\ORM\Models\AddressModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactModel;
use FKSDB\Models\ORM\Models\PostContactType;
use FKSDB\Models\ORM\Services\AddressService;
use FKSDB\Models\ORM\Services\PostContactService;
use Fykosak\Utils\Logging\Message;
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
        $this->getPresenter()->redirect('default');
    }

    protected function setDefaults(): void
    {
        $this->getForm()->setValues(
            ['address' => isset($this->model) ? $this->model->address_id : ReferencedId::VALUE_PROMISE]
        );
    }

    public function handleDelete(): void
    {
        if (!isset($this->model)) {
            $this->flashMessage(_('Address does not exists'), Message::LVL_ERROR);
            return;
        }
        if ($this->postContactType->value === PostContactType::PERMANENT) {
            $this->flashMessage(_('Permanent address cannot be deleted'), Message::LVL_ERROR);
            return;
        }
        $this->addressService->disposeModel($this->model->address);
        $this->postContactService->disposeModel($this->model);
        $this->flashMessage(_('Address has been deleted'), Message::LVL_INFO);
    }

    public function render(): void
    {
        $this->template->type = $this->postContactType;
        $this->template->hasAddress = isset($this->model);
        parent::render();
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . '/layout.address.latte';
    }
}
