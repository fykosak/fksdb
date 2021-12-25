<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\ModelPostContact;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\ServiceAddress;
use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Models\ORM\Services\ServicePersonInfo;
use FKSDB\Models\ORM\Services\ServicePostContact;
use FKSDB\Models\Utils\FormUtils;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;

/**
 * @property ModelPerson|null $model
 */
class PersonFormComponent extends AbstractEntityFormComponent
{

    public const POST_CONTACT_DELIVERY = 'post_contact_d';
    public const POST_CONTACT_PERMANENT = 'post_contact_p';

    public const PERSON_CONTAINER = 'person';
    public const PERSON_INFO_CONTAINER = 'person_info';

    private SingleReflectionFormFactory $singleReflectionFormFactory;
    private AddressFactory $addressFactory;
    private ServicePerson $servicePerson;
    private ServicePersonInfo $servicePersonInfo;
    private ServicePostContact $servicePostContact;
    private ServiceAddress $serviceAddress;
    private MemoryLogger $logger;
    private FieldLevelPermission $userPermission;

    public function __construct(Container $container, int $userPermission, ?ModelPerson $person)
    {
        parent::__construct($container, $person);
        $this->userPermission = new FieldLevelPermission($userPermission, $userPermission);
        $this->logger = new MemoryLogger();
    }

    final public function injectFactories(
        SingleReflectionFormFactory $singleReflectionFormFactory,
        ServicePerson $servicePerson,
        ServicePersonInfo $servicePersonInfo,
        AddressFactory $addressFactory,
        ServicePostContact $servicePostContact,
        ServiceAddress $serviceAddress
    ): void {
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
        $this->servicePerson = $servicePerson;
        $this->servicePersonInfo = $servicePersonInfo;
        $this->addressFactory = $addressFactory;
        $this->servicePostContact = $servicePostContact;
        $this->serviceAddress = $serviceAddress;
    }

    public static function mapAddressContainerNameToType(string $containerName): string
    {
        switch ($containerName) {
            case self::POST_CONTACT_PERMANENT:
                return ModelPostContact::TYPE_PERMANENT;
            case self::POST_CONTACT_DELIVERY:
                return ModelPostContact::TYPE_DELIVERY;
            default:
                throw new InvalidArgumentException();
        }
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
        $fields = $this->getContext()->getParameters()['common']['editPerson'];
        foreach ($fields as $table => $rows) {
            switch ($table) {
                case self::PERSON_INFO_CONTAINER:
                case self::PERSON_CONTAINER:
                    $control = $this->singleReflectionFormFactory->createContainerWithMetadata(
                        $table,
                        $rows,
                        $this->userPermission
                    );
                    break;
                case self::POST_CONTACT_DELIVERY:
                case self::POST_CONTACT_PERMANENT:
                    $control = $this->addressFactory->createAddressContainer($table);
                    break;
                default:
                    throw new InvalidArgumentException();
            }
            $form->addComponent($control, $table);
        }
    }

    protected function handleFormSuccess(Form $form): void
    {
        $connection = $this->servicePerson->explorer->getConnection();
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull($values, true);
        $connection->beginTransaction();
        $this->logger->clear();
        /** @var ModelPerson $person */
        $person = $this->servicePerson->storeModel($data[self::PERSON_CONTAINER], $this->model);
        $this->servicePersonInfo->storeModel(
            array_merge($data[self::PERSON_INFO_CONTAINER], ['person_id' => $person->person_id,]),
            $person->getInfo()
        );
        $this->storeAddresses($person, $data);

        $connection->commit();
        $this->logger->log(
            new Message(
                isset($this->model) ? _('Data has been updated') : _('Person has been created'),
                Message::LVL_SUCCESS
            )
        );
        FlashMessageDump::dump($this->logger, $this->getPresenter(), true);
        $this->getPresenter()->redirect('this');
    }

    /**
     * @throws BadTypeException
     */
    protected function setDefaults(): void
    {
        if (isset($this->model)) {
            $this->getForm()->setDefaults([
                self::PERSON_CONTAINER => $this->model->toArray(),
                self::PERSON_INFO_CONTAINER => $this->model->getInfo() ? $this->model->getInfo()->toArray() : null,
                self::POST_CONTACT_DELIVERY => $this->model->getDeliveryAddress() ?? [],
                self::POST_CONTACT_PERMANENT => $this->model->getPermanentAddress() ?? [],
            ]);
        }
    }

    private function storeAddresses(ModelPerson $person, array $data): void
    {
        foreach ([self::POST_CONTACT_DELIVERY, self::POST_CONTACT_PERMANENT] as $type) {
            $datum = FormUtils::removeEmptyValues($data[$type]);
            $shortType = self::mapAddressContainerNameToType($type);
            $oldAddress = $person->getAddress($shortType);
            if (count($datum)) {
                if ($oldAddress) {
                    $this->serviceAddress->updateModel($oldAddress, $datum);
                    $this->logger->log(new Message(_('Address has been updated'), Message::LVL_INFO));
                } else {
                    $address = $this->serviceAddress->createNewModel($datum);
                    $postContactData = [
                        'type' => $shortType,
                        'person_id' => $person->person_id,
                        'address_id' => $address->address_id,
                    ];
                    $this->servicePostContact->createNewModel($postContactData);
                    $this->logger->log(new Message(_('Address has been created'), Message::LVL_INFO));
                }
            } elseif ($oldAddress) {
                $this->servicePostContact->getTable()->where([
                    'type' => $shortType,
                    'person_id' => $person->person_id,
                ])->delete();
                $oldAddress->delete();
                $this->logger->log(new Message(_('Address has been deleted'), Message::LVL_INFO));
            }
        }
    }
}
