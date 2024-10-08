<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Controls\ReferencedIdMode;
use FKSDB\Components\Forms\Referenced\Address\AddressDataContainer;
use FKSDB\Components\Forms\Referenced\Address\AddressHandler;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactModel;
use FKSDB\Models\ORM\Models\PostContactType;
use FKSDB\Models\ORM\Services\AddressService;
use FKSDB\Models\ORM\Services\Exceptions\InvalidAddressException;
use FKSDB\Models\ORM\Services\Exceptions\InvalidPostalCode;
use FKSDB\Models\ORM\Services\PostContactService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\InvalidStateException;

/**
 * @phpstan-extends ModelForm<PostContactModel,array{address:array<string,mixed>}>
 */
class AddressFormComponent extends ModelForm
{
    public const CONTAINER = 'address';

    private PostContactType $postContactType;
    private AddressService $addressService;
    private PostContactService $postContactService;
    private PersonModel $person;

    public function __construct(Container $container, PostContactType $postContactType, PersonModel $person)
    {
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
        $form->addComponent(new AddressDataContainer($this->container, false, true), self::CONTAINER);
    }

    /**
     * @throws \Throwable
     */
    protected function innerSuccess(array $values, Form $form): PostContactModel
    {
        $address = (new AddressHandler($this->container))->store(
        /** @phpstan-ignore-next-line */
            $values[self::CONTAINER],
            isset($this->model) ? $this->model->address : null
        );
        if (!$address) {
            throw new InvalidStateException(_('Address is required'));
        }
        if (!isset($this->model)) {
            $postContactModel = $this->postContactService->storeModel(
                [
                    'type' => $this->postContactType->value,
                    'address_id' => $address->address_id,
                    'person_id' => $this->person->person_id,
                ],
                $this->model
            );
        }
        return $postContactModel ?? $this->model;
    }

    protected function onException(\Throwable $exception): bool
    {
        if ($exception instanceof InvalidAddressException || $exception instanceof InvalidPostalCode) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            return true;
        }
        return parent::onException($exception);
    }

    protected function setDefaults(Form $form): void
    {
        /** @var AddressDataContainer $container */
        $container = $form->getComponent(self::CONTAINER);
        $container->setModel(
            isset($this->model) ? $this->model->address : null,
            ReferencedIdMode::from(ReferencedIdMode::NORMAL)
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
        $this->template->hasAddress = isset($this->model);
        $this->template->type = $this->postContactType;
        parent::render();
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . '/layout.address.latte';
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(_('Address has been saved'));
        $this->getPresenter()->redirect('default');
    }
}
