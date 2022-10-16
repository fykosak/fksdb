<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\PostContactType;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\AddressService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\ORM\Services\PersonInfoService;
use FKSDB\Models\ORM\Services\PostContactService;
use FKSDB\Models\Utils\FormUtils;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;

/**
 * @property PersonModel|null $model
 */
class PersonFormComponent extends EntityFormComponent
{
    public const POST_CONTACT_DELIVERY = 'post_contact_d';
    public const POST_CONTACT_PERMANENT = 'post_contact_p';

    public const PERSON_CONTAINER = 'person';
    public const PERSON_INFO_CONTAINER = 'person_info';

    private SingleReflectionFormFactory $singleReflectionFormFactory;
    private AddressFactory $addressFactory;
    private PersonService $personService;
    private PersonInfoService $personInfoService;
    private PostContactService $postContactService;
    private AddressService $addressService;
    private MemoryLogger $logger;
    private FieldLevelPermission $userPermission;

    public function __construct(Container $container, int $userPermission, ?PersonModel $person)
    {
        parent::__construct($container, $person);
        $this->userPermission = new FieldLevelPermission($userPermission, $userPermission);
        $this->logger = new MemoryLogger();
    }

    final public function injectFactories(
        SingleReflectionFormFactory $singleReflectionFormFactory,
        PersonService $personService,
        PersonInfoService $personInfoService,
        AddressFactory $addressFactory,
        PostContactService $postContactService,
        AddressService $addressService
    ): void {
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
        $this->personService = $personService;
        $this->personInfoService = $personInfoService;
        $this->addressFactory = $addressFactory;
        $this->postContactService = $postContactService;
        $this->addressService = $addressService;
    }

    public static function mapAddressContainerNameToType(string $containerName): PostContactType
    {
        switch ($containerName) {
            case self::POST_CONTACT_PERMANENT:
                return PostContactType::tryFrom(PostContactType::PERMANENT);
            case self::POST_CONTACT_DELIVERY:
                return PostContactType::tryFrom(PostContactType::DELIVERY);
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
        $fields = $this->getContext()->getParameters()['forms']['adminPerson'];
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
        $connection = $this->personService->explorer->getConnection();
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull2($values);
        $connection->beginTransaction();
        $this->logger->clear();
        $person = $this->personService->storeModel($data[self::PERSON_CONTAINER], $this->model);
        $this->personInfoService->storeModel(
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
                self::POST_CONTACT_DELIVERY =>
                    $this->model->getAddress(PostContactType::tryFrom(PostContactType::DELIVERY)) ?? [],
                self::POST_CONTACT_PERMANENT =>
                    $this->model->getAddress(PostContactType::tryFrom(PostContactType::PERMANENT)) ?? [],
            ]);
        }
    }

    private function storeAddresses(PersonModel $person, array $data): void
    {
        foreach ([self::POST_CONTACT_DELIVERY, self::POST_CONTACT_PERMANENT] as $type) {
            $datum = FormUtils::removeEmptyValues($data[$type]);
            $shortType = self::mapAddressContainerNameToType($type);
            $oldAddress = $person->getAddress($shortType);
            if (count($datum)) {
                if ($oldAddress) {
                    $this->addressService->storeModel($datum, $oldAddress);
                    $this->logger->log(new Message(_('Address has been updated'), Message::LVL_INFO));
                } else {
                    $address = $this->addressService->storeModel($datum);
                    $postContactData = [
                        'type' => $shortType->value,
                        'person_id' => $person->person_id,
                        'address_id' => $address->address_id,
                    ];
                    $this->postContactService->storeModel($postContactData);
                    $this->logger->log(new Message(_('Address has been created'), Message::LVL_INFO));
                }
            } elseif ($oldAddress) {
                $postContact = $person->getPostContact($shortType);
                $this->postContactService->disposeModel($postContact);
                $this->addressService->disposeModel($oldAddress);
                $this->logger->log(new Message(_('Address has been deleted'), Message::LVL_INFO));
            }
        }
    }
}
