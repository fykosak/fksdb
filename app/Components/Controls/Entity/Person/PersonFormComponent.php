<?php

namespace FKSDB\Components\Controls\Entity\Person;

use FKSDB\Components\Controls\Entity\AbstractEntityFormComponent;
use FKSDB\Components\Controls\Entity\IEditEntityForm;
use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\OmittedControlException;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Config\GlobalParameters;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\ModelException;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\MemoryLogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPersonInfo;
use FKSDB\ORM\Models\ModelPostContact;
use FKSDB\ORM\Services\ServiceAddress;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ORM\Services\ServicePersonInfo;
use FKSDB\ORM\Services\ServicePostContact;
use FKSDB\ORM\ServicesMulti\ServiceMPostContact;
use FKSDB\Utils\FormUtils;
use Nette\Application\AbortException;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Forms\Form;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Tracy\Debugger;

/**
 * Class AbstractPersonFormControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonFormComponent extends AbstractEntityFormComponent implements IEditEntityForm {

    const POST_CONTACT_DELIVERY = 'post_contact_d';
    const POST_CONTACT_PERMANENT = 'post_contact_p';

    const PERSON_CONTAINER = 'person';
    const PERSON_INFO_CONTAINER = 'person_info';
    /**
     * @var SingleReflectionFormFactory
     */
    protected $singleReflectionFormFactory;
    /**
     * @var AddressFactory
     */
    protected $addressFactory;
    /**
     * @var GlobalParameters
     */
    protected $globalParameters;
    /**
     * @var ServicePerson
     */
    protected $servicePerson;
    /**
     * @var ServicePersonInfo
     */
    protected $servicePersonInfo;
    /**
     * @var ServiceMPostContact
     */
    private $servicePostContact;
    /** @var ServiceAddress */
    private $serviceAddress;
    /** @var MemoryLogger */
    private $logger;
    /**
     * @var FieldLevelPermission
     */
    private $userPermission;
    /**
     * @var ModelPerson
     */
    private $model;

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

    /**
     * @param GlobalParameters $globalParameters
     * @param SingleReflectionFormFactory $singleReflectionFormFactory
     * @param ServicePerson $servicePerson
     * @param ServicePersonInfo $servicePersonInfo
     * @param AddressFactory $addressFactory
     * @param ServicePostContact $servicePostContact
     * @param ServiceAddress $serviceAddress
     * @return void
     */
    public function injectFactories(
        GlobalParameters $globalParameters,
        SingleReflectionFormFactory $singleReflectionFormFactory,
        ServicePerson $servicePerson,
        ServicePersonInfo $servicePersonInfo,
        AddressFactory $addressFactory,
        ServicePostContact $servicePostContact,
        ServiceAddress $serviceAddress
    ) {
        $this->globalParameters = $globalParameters;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
        $this->servicePerson = $servicePerson;
        $this->servicePersonInfo = $servicePersonInfo;
        $this->addressFactory = $addressFactory;
        $this->servicePostContact = $servicePostContact;
        $this->serviceAddress = $serviceAddress;
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbstractColumnException
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form) {
        $fields = $this->globalParameters['common']['editPerson'];
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
     * @param AbstractModelSingle|ModelPerson $model
     * @return void
     * @throws BadTypeException
     */
    public function setModel(AbstractModelSingle $model) {
        $this->model = $model;
        $this->getForm()->setDefaults([
            self::PERSON_CONTAINER => $model->toArray(),
            self::PERSON_INFO_CONTAINER => $model->getInfo() ? $model->getInfo()->toArray() : null,
            self::POST_CONTACT_DELIVERY => $model->getDeliveryAddress2() ?: [],
            self::POST_CONTACT_PERMANENT => $model->getPermanentAddress2() ?: [],
        ]);
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    protected function handleFormSuccess(Form $form) {
        $connection = $this->servicePerson->getConnection();
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull($values, true);
        try {
            $connection->beginTransaction();
            $this->logger->clear();
            $person = $this->servicePerson->store($this->create ? null : $this->model, $data[self::PERSON_CONTAINER]);
            $this->servicePersonInfo->store($person, $person->getInfo(), $data[self::PERSON_INFO_CONTAINER]);
            $this->storeAddresses($person, $data);

            $connection->commit();
            $this->logger->log(new Message($this->create ? _('Person has been created') : _('Data has been updated'), Message::LVL_SUCCESS));
            FlashMessageDump::dump($this->logger, $this->getPresenter(), true);
            $this->getPresenter()->redirect('this');
        } catch (ModelException $exception) {
            $connection->rollBack();
            $previous = $exception->getPrevious();
            if ($previous && $previous instanceof UniqueConstraintViolationException) {
                $this->flashMessage(sprintf(_('Person with same data already exists: "%s"'), $previous->errorInfo[2] ?? ''), Message::LVL_DANGER);
                return;
            }
            Debugger::log($exception);
            $this->flashMessage(_('Error'), Message::LVL_DANGER);
        }
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
     * @param ModelPerson $person
     * @param array $data
     * @return void
     */
    private function storeAddresses(ModelPerson $person, array $data) {
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
