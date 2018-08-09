<?php

use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Factories\FlagFactory;
use FKSDB\Components\Forms\Factories\PersonAccommodationFactory;
use FKSDB\Components\Forms\Factories\PersonHistoryFactory;
use FKSDB\Components\Forms\Factories\PersonInfoFactory;
use Persons\ReferencedPersonHandler;

class MainPersonFactory {


    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var FlagFactory
     */
    private $personFlagFactory;

    /**
     * @var PersonHistoryFactory
     */
    private $personHistoryFactory;

    /**
     * @var PersonInfoFactory
     */
    private $personInfoFactory;

    /**
     * @var PersonAccommodationFactory
     */
    private $personAccommodationFactory;

    function __construct(PersonAccommodationFactory $personAccommodationFactory, ServicePerson $servicePerson, FlagFactory $flagFactory, PersonHistoryFactory $personHistoryFactory, PersonInfoFactory $personInfoFactory, PersonFactory $personFactory) {
        $this->personAccommodationFactory = $personAccommodationFactory;
        $this->personFactory = $personFactory;
        $this->personFlagFactory = $flagFactory;
        $this->personHistoryFactory = $personHistoryFactory;
        $this->personInfoFactory = $personInfoFactory;
    }

    /**
     * @param $sub
     * @param $fieldName
     * @param $acYear
     * @param ReactField $hiddenField
     * @param array $metadata
     * @return ReactField
     */
    public function createField($sub, $fieldName, $acYear, ReactField $hiddenField, $metadata = []) {
        /**
         * @var $control ReactField
         */
        $control = null;
        switch ($sub) {
            case ReferencedPersonHandler::POST_CONTACT_DELIVERY:
            case ReferencedPersonHandler::POST_CONTACT_PERMANENT:
                throw new \Nette\NotImplementedException('Adresa nieje implementovaná');
            /*if ($fieldName == 'address') {
                $required = \Nette\Utils\Arrays::get($metadata, 'required', false);
                if ($required) {
                    $options = \FKSDB\Components\Forms\Factories\AddressFactory::REQUIRED;
                } else {
                    $options = 0;
                }
                $container = $this->reactPersonAddressFactory->createAddress($options, $hiddenField);
                return $container;
            } else {
                throw new InvalidArgumentException("Only 'address' field is supported.");
            }
            break;*/
            case 'person_has_flag':
                $control = $this->personFlagFactory->createReactField();
                break;
            case 'person_history' :
                $control = $this->personHistoryFactory->createReactField($fieldName, $acYear);
                break;
            case 'person_info':
                $control = $this->personInfoFactory->createReactField($fieldName);
                break;
            case 'person' :
                $control = $this->personFactory->createReactField($fieldName);
                break;
            case 'person_accommodation':
                $control = $this->personAccommodationFactory->createMatrixSelect($metadata);
                break;
            //throw new NotImplementedException('Ubytovanie ešte nieje implentované');
            default:
                throw new InvalidArgumentException('Pre ' . $sub . ' neexistuje továrnička');

        }
        foreach ($metadata as $key => $value) {

            switch ($key) {
                case 'required':
                    if ($value) {
                        $hiddenField->setRequired(true);
                    }
                    $control->setRequired($value);
                    break;
                case 'caption':
                    $control->setLabel($value);
                    break;
                case 'description':
                    $control->setDescription($value);
                    break;
                default:
                    break;
            }
        }
        return $control;
    }
}
