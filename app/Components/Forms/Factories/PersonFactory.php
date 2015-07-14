<?php

namespace FKSDB\Components\Forms\Factories;

use FKS\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKS\Components\Forms\Controls\Autocomplete\IDataProvider;
use FKS\Components\Forms\Controls\URLTextBox;
use FKS\Components\Forms\Controls\WriteonlyDatePicker;
use FKS\Components\Forms\Controls\WriteonlyInput;
use FKS\Localization\GettextTranslator;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\PersonInfoContainer;
use FKSDB\Components\Forms\Rules\BornNumber;
use FKSDB\Components\Forms\Rules\UniqueEmailFactory;
use ModelPerson;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\Utils\Arrays;
use Nette\Utils\Html;
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
     *
     * @var YearCalculator
     */
    private $yearCalculator;

    function __construct(GettextTranslator $translator, UniqueEmailFactory $uniqueEmailFactory, SchoolFactory $factorySchool, ServicePerson $servicePerson, AddressFactory $addressFactory, YearCalculator $yearCalculator) {
        $this->translator = $translator;
        $this->uniqueEmailFactory = $uniqueEmailFactory;
        $this->factorySchool = $factorySchool;
        $this->servicePerson = $servicePerson;
        $this->addressFactory = $addressFactory;
        $this->yearCalculator = $yearCalculator;
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
        } else {
            $methodName = 'create' . str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));
            $control = call_user_func(array($this, $methodName), $acYear);

            if (Arrays::get($metadata, 'required', false)) {
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
            if ($caption = Arrays::get($metadata, 'caption', null)) { // intentionally =
                $control->caption = $caption;
            }
            if ($description = Arrays::get($metadata, 'description', null)) { // intentionally =
                $control->setOption('description', $description);
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

    public function createOtherName($acYear = null) {
        return (new TextInput(_('Jméno')));
    }

    public function createFamilyName($acYear = null) {
        return (new TextInput(_('Příjmení')));
    }

    public function createDisplayName($acYear = null) {
        return (new TextInput(_('Zobrazované jméno')))
                        ->setOption('description', _('Pouze pokud je odlišené od "jméno příjmení".'));
    }

    public function createGender($acYear = null) {
        return (new RadioList(_('Pohlaví'), array('M' => 'muž', 'F' => 'žena')))
                        ->setDefaultValue('M');
    }

    /*
     * Person info
     */

    public function createBorn($acYear = null) {
        return (new WriteonlyDatePicker(_('Datum narození')));
    }

    public function createIdNumber($acYear = null) {
        return (new WriteonlyInput(_('Číslo OP')))
                        ->setOption('description', _('U cizinců číslo pasu.'))
                        ->addRule(Form::MAX_LENGTH, null, 32);
    }

    public function createBornId($acYear = null) {
        $control = new WriteonlyInput(_('Rodné číslo'));
        $control->setOption('description', _('U cizinců prázdné.'))
                ->addCondition(Form::FILLED)
                ->addRule(new BornNumber(), _('Rodné číslo nemá platný formát.'));
        return $control;
    }

   
    
    public function createPhoneParentM($acYear = null) {
        return $this->rawPhone(_('Telefonní číslo (matka)'), $acYear);
    }
    
    public function createPhoneParentD($acYear = null) {
        return $this->rawPhone(_('Telefonní číslo (otec)'), $acYear);
    }
    
    public function createPhone($acYear = null) {
        return $this->rawPhone(_('Telefonní číslo'), $acYear);
    }

    public function createIm($acYear = null) {
        return (new WriteonlyInput(_('ICQ, Jabber, apod.')))
                        ->addRule(Form::MAX_LENGTH, null, 32);
    }

    public function createBirthplace($acYear = null) {
        return (new WriteonlyInput(_('Místo narození')))
                        ->setOption('description', _('Město a okres (kvůli diplomům).'))
                        ->addRule(Form::MAX_LENGTH, null, 255);
    }

    public function createUkLogin($acYear = null) {
        return (new WriteonlyInput(_('Login UK')))
                        ->addRule(Form::MAX_LENGTH, null, 8);
    }

    public function createAccount($acYear = null) {
        return (new WriteonlyInput(_('Číslo bankovního účtu')))
                        ->addRule(Form::MAX_LENGTH, null, 32);
    }

    public function createTexSignature($acYear = null) {
        $control = new TextInput(_('TeX identifikátor'));
        $control->addRule(Form::MAX_LENGTH, null, 32)
                ->addCondition(Form::FILLED)
                ->addRule(Form::REGEXP, _('%label obsahuje nepovolené znaky.'), '/^[a-z][a-z0-9._\-]*$/i');
        return $control;
    }

    public function createDomainAlias($acYear = null) {
        $control = new TextInput(_('Jméno v doméně fykos.cz'));
        $control->addRule(Form::MAX_LENGTH, null, 32)
                ->addCondition(Form::FILLED)
                ->addRule(Form::REGEXP, _('%l obsahuje nepovolené znaky.'), '/^[a-z][a-z0-9._\-]*$/i');
        return $control;
    }

    public function createCareer($acYear = null) {
        return (new TextArea(_('Co právě dělá')))
                        ->setOption('description', _('Zobrazeno v síni slávy'));
    }

    public function createHomepage($acYear = null) {
        return (new URLTextBox(_('Homepage')));
    }

    public function createNote($acYear = null) {
        return (new TextArea(_('Poznámka')));
    }

    public function createOrigin($acYear = null) {
        return (new TextArea(_('Jak jsi se o nás dozvěděl(a)?')));
    }

    public function createAgreed($acYear = null) {
        $link = Html::el('a');
        $link->setText(_('Text souhlasu'));
        $link->href = _("http://fykos.cz/doc/souhlas.pdf");

        return (new Checkbox(_('Souhlasím se zpracováním osobních údajů')))
                        ->setOption('description', $link);
    }

    public function createEmail($acYear = null) {
        $control = new TextInput(_('E-mail'));
        $control->addCondition(Form::FILLED)
                ->addRule(Form::EMAIL, _('Neplatný tvar e-mailu.'));
        return $control;
    }

    private function rawPhone($label, $acYear = null) {
        $control = new WriteonlyInput($label);
        $control->addRule(Form::MAX_LENGTH, null, 32)
                ->addCondition(Form::FILLED)
                //->addRule(Form::REGEXP, _('%label smí obsahovat jen číslice.'), '/(\+?\d{1,3} )?(\d{3} ?){3}/')
                ->addRule(Form::REGEXP, _('%label smí obsahovat jen číslice a musí být v mezinárodím tvaru začínajícím +421 nebo +420.'),'/(\+42[01])?(\s?\d{3}){3}/');
	return $control;
    }

    /*
     * Person history
     */

    public function createStudyYear($acYear) {
        $studyYear = new SelectBox(_('Ročník'));

        $hsYears = array();
        foreach (range(1, 4) as $study_year) {
            $hsYears[$study_year] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'), $study_year, $this->yearCalculator->getGraduationYear($study_year, $acYear));
        }

        $primaryYears = array();
        foreach (range(6, 9) as $study_year) {
            $primaryYears[$study_year] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'), $study_year, $this->yearCalculator->getGraduationYear($study_year, $acYear));
        }

        $studyYear->setItems(array(
                    _('střední škola') => $hsYears,
                    _('základní škola nebo víceleté gymnázium') => $primaryYears,
                ))->setOption('description', _('Kvůli zařazení do kategorie.'))
                ->setPrompt(_('Zvolit ročník'));

        return $studyYear;
    }

    public function createSchoolId($acYear = null) {
        return $this->factorySchool->createSchoolSelect(SchoolFactory::SHOW_UNKNOWN_SCHOOL_HINT);
    }

    public function createClass($acYear = null) {
        return (new TextInput(_('Třída')))
                        ->addRule(Form::MAX_LENGTH, null, 16);
    }

}

