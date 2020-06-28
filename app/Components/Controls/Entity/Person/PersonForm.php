<?php

namespace FKSDB\Components\Controls\Entity\Person;

use FKSDB\Components\Controls\Entity\AbstractEntityFormControl;
use FKSDB\Components\Controls\Entity\IEditEntityForm;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Factories\PersonInfoFactory;
use FKSDB\Config\GlobalParameters;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\ModelException;
use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPostContact;
use FKSDB\ORM\Services\ServiceAddress;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ORM\Services\ServicePersonInfo;
use FKSDB\ORM\Services\ServicePostContact;
use FKSDB\ORM\ServicesMulti\ServiceMPostContact;
use FKSDB\Utils\FormUtils;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Tracy\Debugger;

/**
 * Class AbstractPersonFormControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonForm extends AbstractEntityFormControl implements IEditEntityForm {

    const POST_CONTACT_DELIVERY = 'post_contact_d';
    const POST_CONTACT_PERMANENT = 'post_contact_p';
    /**
     * @var PersonFactory
     */
    protected $personFactory;
    /**
     * @var PersonInfoFactory
     */
    protected $personInfoFactory;
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
     * @param FieldLevelPermission $userPermission is required to model editing, otherwise is seted to 2048,2048
     * @param bool $create
     */
    public function __construct(Container $container, bool $create, FieldLevelPermission $userPermission = null) {
        parent::__construct($container, $create);
        if (is_null($userPermission)) {
            if ($create) {
                $this->userPermission = new FieldLevelPermission(2048, 2048);
            } else {
                throw new InvalidArgumentException();
            }
        } else {
            $this->userPermission = $userPermission;
        }
    }

    /**
     * @param GlobalParameters $globalParameters
     * @param PersonFactory $personFactory
     * @param PersonInfoFactory $personInfoFactory
     * @param ServicePerson $servicePerson
     * @param ServicePersonInfo $servicePersonInfo
     * @param AddressFactory $addressFactory
     * @param ServicePostContact $servicePostContact
     * @param ServiceAddress $serviceAddress
     * @return void
     */
    public function injectFactories(
        GlobalParameters $globalParameters,
        PersonFactory $personFactory,
        PersonInfoFactory $personInfoFactory,
        ServicePerson $servicePerson,
        ServicePersonInfo $servicePersonInfo,
        AddressFactory $addressFactory,
        ServicePostContact $servicePostContact,
        ServiceAddress $serviceAddress
    ) {
        $this->personFactory = $personFactory;
        $this->globalParameters = $globalParameters;
        $this->personInfoFactory = $personInfoFactory;
        $this->servicePerson = $servicePerson;
        $this->servicePersonInfo = $servicePersonInfo;
        $this->addressFactory = $addressFactory;
        $this->servicePostContact = $servicePostContact;
        $this->serviceAddress = $serviceAddress;
    }

    /**
     * @param Form $form
     * @return void
     * @throws \Exception
     */
    protected function configureForm(Form $form) {
        $fields = $this->globalParameters['common']['editPerson'];
        foreach ($fields as $table => $rows) {
            switch ($table) {
                case 'person_info':
                    $control = $this->personInfoFactory->createContainerWithMetadata($rows, $this->userPermission);
                    break;
                case 'person':
                    $control = $this->personFactory->createContainerWithMetadata($rows, $this->userPermission);
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
            'person' => $model->toArray(),
            'person_info' => $model->getInfo() ? $model->getInfo()->toArray() : null,
            self::POST_CONTACT_DELIVERY => $model->getDeliveryAddress2() ?: [],
            self::POST_CONTACT_PERMANENT => $model->getPermanentAddress2() ?: [],
        ]);
    }

    /**
     * @param Form $form
     * @return void
     * @throws \Exception
     */
    protected function handleFormSuccess(Form $form) {
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull($values, true);
        try {
            $this->create ? $this->handleCreateSuccess($data) : $this->handleEditSuccess($data);
        } catch (ModelException $exception) {
            Debugger::log($exception);
            $this->flashMessage(_('Error'), Message::LVL_DANGER);
        }
    }

    /**
     * @param array $data
     * @return mixed|void
     * @throws AbortException
     */
    protected function handleCreateSuccess(array $data) {
        $person = $this->servicePerson->createNewModel($data['person']);
        $personInfoData = $data['person_info'];

        $personInfoData['person_id'] = $person->person_id;
        $this->servicePersonInfo->createNewModel($personInfoData);

        $this->handleSaveAddresses($data, $person);
        $this->flashMessage(_('Person has been created'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('this');
    }

    /**
     * @param array $data
     * @return void
     * @throws AbortException
     * @throws \Exception
     */
    protected function handleEditSuccess(array $data) {
        Debugger::barDump($data);
        $this->servicePerson->updateModel2($this->model, $data['person']);
        $personInfoData = $data['person_info'];
        if ($this->model->getInfo()) {
            $this->servicePersonInfo->updateModel2($this->model->getInfo(), $personInfoData);
        } else {
            $personInfoData['person_id'] = $this->model->person_id;
            $this->servicePersonInfo->createNewModel($personInfoData);
        }
        $this->handleSaveAddresses($data, $this->model);

        $this->flashMessage(_('Data has been saved'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('this');
    }

    /**
     * @param array $data
     * @param ModelPerson $person
     * @return void
     */
    private function handleSaveAddresses(array $data, ModelPerson $person) {
        foreach ([self::POST_CONTACT_DELIVERY, self::POST_CONTACT_PERMANENT] as $type) {
            $datum = FormUtils::removeEmptyValues($data[$type]);
            $shortType = ($type === self::POST_CONTACT_PERMANENT) ? ModelPostContact::TYPE_PERMANENT : ModelPostContact::TYPE_DELIVERY;
            if (count($datum)) {
                $oldAddress = $person->getAddress2($shortType);
                if ($oldAddress) {
                    $this->serviceAddress->updateModel2($oldAddress, $datum);
                } else {
                    $address = $this->serviceAddress->createNewModel($datum);
                    $postContactData = [
                        'type' => $shortType,
                        'person_id' => $person->person_id,
                        'address_id' => $address->address_id,
                    ];
                    $this->servicePostContact->createNewModel($postContactData);
                }
            }
        }
    }
}
