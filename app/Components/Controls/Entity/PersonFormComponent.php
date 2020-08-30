<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\DBReflection\ColumnFactories\AbstractColumnException;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\DBReflection\OmittedControlException;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\MemoryLogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPostContact;
use FKSDB\ORM\Services\ServiceAddress;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ORM\Services\ServicePersonInfo;
use FKSDB\ORM\Services\ServicePostContact;
use FKSDB\Utils\FormUtils;
use Nette\Application\AbortException;
use Nette\Forms\Form;
use Nette\DI\Container;
use Nette\InvalidArgumentException;

/**
 * Class AbstractPersonFormControl
 * @author Michal Červeňák <miso@fykos.cz>
 * @property ModelPerson $model
 */
class PersonFormComponent extends EditEntityFormComponent {

    public const POST_CONTACT_DELIVERY = 'post_contact_d';
    public const POST_CONTACT_PERMANENT = 'post_contact_p';

    public const PERSON_CONTAINER = 'person';
    public const PERSON_INFO_CONTAINER = 'person_info';

    protected SingleReflectionFormFactory $singleReflectionFormFactory;

    protected AddressFactory $addressFactory;

    protected ServicePerson $servicePerson;

    protected ServicePersonInfo $servicePersonInfo;

    private ServicePostContact $servicePostContact;

    private ServiceAddress $serviceAddress;

    private MemoryLogger $logger;

    private FieldLevelPermission $userPermission;

    /**
     * AbstractPersonFormControl constructor.
     * @param Container $container
     * @param int $userPermission is required to model editing, otherwise is setted to 2048
     * @param bool $create
     */
    public function __construct(Container $container, bool $create, int $userPermission) {
        parent::__construct($container, $create);
        $this->userPermission = new FieldLevelPermission($userPermission, $userPermission);
        $this->logger = new MemoryLogger();
    }

    public function injectFactories(
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

    public static function mapAddressContainerNameToType(string $containerName): string {
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
     * @param Form $form
     * @return void
     * @throws AbstractColumnException
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void {
        $fields = $this->getContext()->getParameters()['common']['editPerson'];
        foreach ($fields as $table => $rows) {
            switch ($table) {
                case self::PERSON_INFO_CONTAINER:
                case self::PERSON_CONTAINER:
                    $control = $this->singleReflectionFormFactory->createContainerWithMetadata($table, $rows, $this->userPermission);
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

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    protected function handleFormSuccess(Form $form): void {
        $connection = $this->servicePerson->getConnection();
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull($values, true);
        $connection->beginTransaction();
        $this->logger->clear();
        $person = $this->servicePerson->store($this->create ? null : $this->model, $data[self::PERSON_CONTAINER]);
        $this->servicePersonInfo->store($person, $person->getInfo(), $data[self::PERSON_INFO_CONTAINER]);
        $this->storeAddresses($person, $data);

        $connection->commit();
        $this->logger->log(new Message($this->create ? _('Person has been created') : _('Data has been updated'), Message::LVL_SUCCESS));
        FlashMessageDump::dump($this->logger, $this->getPresenter(), true);
        $this->getPresenter()->redirect('this');
    }

    /**
     * @param AbstractModelSingle|ModelPerson|null $model
     * @return void
     * @throws BadTypeException
     */
    protected function setDefaults(?AbstractModelSingle $model): void {
        if (!is_null($model)) {
            $this->getForm()->setDefaults([
                self::PERSON_CONTAINER => $model->toArray(),
                self::PERSON_INFO_CONTAINER => $model->getInfo() ? $model->getInfo()->toArray() : null,
                self::POST_CONTACT_DELIVERY => $model->getDeliveryAddress2() ?: [],
                self::POST_CONTACT_PERMANENT => $model->getPermanentAddress2() ?: [],
            ]);
        }
    }

    private function storeAddresses(ModelPerson $person, array $data): void {
        foreach ([self::POST_CONTACT_DELIVERY, self::POST_CONTACT_PERMANENT] as $type) {
            $datum = FormUtils::removeEmptyValues($data[$type]);
            $shortType = self::mapAddressContainerNameToType($type);
            $oldAddress = $person->getAddress2($shortType);
            if (count($datum)) {
                if ($oldAddress) {
                    $this->serviceAddress->updateModel2($oldAddress, $datum);
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
