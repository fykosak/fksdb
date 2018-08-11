<?php

namespace FKSDB\Components\Forms\Factories;

use FKS\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKS\Components\Forms\Controls\Autocomplete\IDataProvider;
use FKS\Localization\GettextTranslator;
use FKSDB\Components\Forms\Factories\Person\DisplayNameField;
use FKSDB\Components\Forms\Factories\Person\FamilyNameField;
use FKSDB\Components\Forms\Factories\Person\GenderField;
use FKSDB\Components\Forms\Factories\Person\OtherNameField;
use FKSDB\Components\Forms\Rules\UniqueEmailFactory;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\Utils\Arrays;
use Persons\ReferencedPersonHandler;
use ServicePerson;
use YearCalculator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonFactory {
    // For person

    const SHOW_DISPLAY_NAME = 0x1;
    const SHOW_GENDER = 0x2;
    const DISABLED = 0x4;

    // For person_info
    /** @const Show iformation important for organizers. */
    const SHOW_ORG_INFO = 0x8;
    const SHOW_EMAIL = 0x10;
    const REQUIRE_AGREEMENT = 0x20;
    const SHOW_LOGIN_CREATION = 0x40;
    /** @const Display origin and agreement only (supplement to other form containers). */
    const SHOW_LIKE_SUPPLEMENT = 0x100;
    const REQUIRE_EMAIL = 0x200;

    // For person_history
    const REQUIRE_SCHOOL = 0x400;
    const REQUIRE_STUDY_YEAR = 0x800;
    /** @const Display school, study year and class only (supplement to other form containers). */
    const SHOW_LIKE_CONTESTANT = 0x1000;

    /* Encapsulation condition argument (workaround) */
    const IDX_CONTROL = 'control';
    const IDX_OPERATION = 'op';
    const IDX_VALUE = 'val';

    /* Subcontainers names */
    const CONT_LOGIN = 'logincr';

    /* Element names */
    const EL_CREATE_LOGIN = 'createLogin';
    const EL_CREATE_LOGIN_LANG = 'lang';

    /**
     *
     * @var GettextTranslator
     */
    private $translator;

    /**
     * @var UniqueEmailFactory
     */
    private $uniqueEmailFactory;

    /**
     * @var SchoolFactory
     */
    private $factorySchool;

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var FlagFactory
     */
    private $flagFactory;

    /**
     *
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * @var PersonHistoryFactory
     */
    private $personHistoryFactory;
    /**
     * @var PersonInfoFactory
     */
    private $personInfoFactory;

    function __construct(GettextTranslator $translator, UniqueEmailFactory $uniqueEmailFactory, SchoolFactory $factorySchool, ServicePerson $servicePerson, AddressFactory $addressFactory, FlagFactory $flagFactory, YearCalculator $yearCalculator, PersonInfoFactory $personInfoFactory, PersonHistoryFactory $personHistoryFactory) {
        $this->translator = $translator;
        $this->uniqueEmailFactory = $uniqueEmailFactory;
        $this->factorySchool = $factorySchool;
        $this->servicePerson = $servicePerson;
        $this->addressFactory = $addressFactory;
        $this->flagFactory = $flagFactory;
        $this->yearCalculator = $yearCalculator;
        $this->personHistoryFactory = $personHistoryFactory;
        $this->personInfoFactory = $personInfoFactory;
    }

    public function createPersonSelect($ajax, $label, IDataProvider $dataProvider, $renderMethod = null) {
        if ($renderMethod === null) {
            $renderMethod = '$("<li>")
                        .append("<a>" + item.label + "<br>" + item.place + ", ID: " + item.value + "</a>")
                        .appendTo(ul);';
        }
        $select = new AutocompleteSelectBox($ajax, $label, $renderMethod);
        $select->setDataProvider($dataProvider);
        return $select;
    }

    public function createField($sub, $fieldName, $acYear, HiddenField $hiddenField = null, $metadata = array()) {
        if (in_array($sub, array(
            ReferencedPersonHandler::POST_CONTACT_DELIVERY,
            ReferencedPersonHandler::POST_CONTACT_PERMANENT,
        ))) {
            if ($fieldName == 'address') {
                $required = Arrays::get($metadata, 'required', false);
                if ($required) {
                    $options = AddressFactory::REQUIRED;
                } else {
                    $options = 0;
                }
                $container = $this->addressFactory->createAddress($options, $hiddenField);
                return $container;
            } else {
                throw new InvalidArgumentException("Only 'address' field is supported.");
            }
        } else if ($sub == 'person_has_flag') {
            $control = $this->flagFactory->createFlag($fieldName, $acYear, $hiddenField, $metadata);
            return $control;
        } else {
            $control = null;
            switch ($sub) {
                case 'person_info':
                    $control = $this->personInfoFactory->createField($fieldName);
                    break;
                case 'person_history':
                    $control = $this->personHistoryFactory->createField($fieldName, $acYear);
                    break;
                default:
                    $methodName = 'create' . str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));
                    $control = call_user_func(array($this, $methodName), $acYear);
            }
            foreach ($metadata as $key => $value) {
                switch ($key) {
                    case 'required':
                        if ($value) {
                            $conditioned = $control;
                            if ($hiddenField) {
                                $conditioned = $control->addConditionOn($hiddenField, Form::FILLED);
                            }
                            if ($fieldName == 'agreed') { // NOTE: this may need refactoring when more customization requirements occurre
                                $conditioned->addRule(Form::FILLED, _('Bez souhlasu nelze bohužel pokračovat.'));
                            } else {
                                $conditioned->addRule(Form::FILLED, _('Pole %label je povinné.'));
                            }
                        }
                        break;
                    case 'caption':
                        if ($value) {
                            $control->caption = $value;
                        }
                        break;
                    case 'description':
                        if ($value) {
                            $control->setOption('description', $value);
                        }
                }
            }
            return $control;
        }
    }

    /*     * ******************************
     * Single field factories
     * ****************************** */

    /*
     * Person
     */

    public function createOtherName() {
        return new OtherNameField();
    }

    public function createFamilyName() {
        return new FamilyNameField();
    }

    public function createDisplayName() {
        return new DisplayNameField();
    }

    public function createGender() {
        return new GenderField();
    }
}

