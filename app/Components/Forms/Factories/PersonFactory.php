<?php

namespace FKSDB\Components\Forms\Factories;

use FKS\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKS\Components\Forms\Controls\Autocomplete\IDataProvider;
use FKS\Components\Forms\Controls\URLTextBox;
use FKS\Localization\GettextTranslator;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\PersonInfoContainer;
use FKSDB\Components\Forms\Rules\BornNumber;
use FKSDB\Components\Forms\Rules\UniqueEmailFactory;
use JanTvrdik\Components\DatePicker;
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
use Nette\Utils\Arrays;
use Nette\Utils\Html;
use ServicePerson;

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

    function __construct(GettextTranslator $translator, UniqueEmailFactory $uniqueEmailFactory, SchoolFactory $factorySchool, ServicePerson $servicePerson, AddressFactory $addressFactory) {
        $this->translator = $translator;
        $this->uniqueEmailFactory = $uniqueEmailFactory;
        $this->factorySchool = $factorySchool;
        $this->servicePerson = $servicePerson;
        $this->addressFactory = $addressFactory;
    }

    public function createPerson($options = 0, ControlGroup $group = null, array $requiredCondition = null) {
        $disabled = (bool) ($options & self::DISABLED);

        $container = new ModelContainer();
        $container->setCurrentGroup($group);


        $control = $this->createOtherName()
                ->setDisabled($disabled)
                ->setOption('description', _('Příp. další jména oddělená mezerou.'));
        $container->addComponent($control, 'other_name');

        if ($requiredCondition) {
            $rules = $control->addConditionOn($requiredCondition[self::IDX_CONTROL], $requiredCondition[self::IDX_OPERATION], $requiredCondition[self::IDX_VALUE]);
        } else {
            $rules = $control;
        }
        $rules->addRule(Form::FILLED, _('Křestní jméno je povinné.'));



        $control = $this->createFamilyName()
                ->setDisabled($disabled)
                ->setOption('description', _('Příp. další jména oddělená mezerou.'));
        $container->addComponent($control, 'family_name');

        if ($requiredCondition) {
            $rules = $control->addConditionOn($requiredCondition[self::IDX_CONTROL], $requiredCondition[self::IDX_OPERATION], $requiredCondition[self::IDX_VALUE]);
        } else {
            $rules = $control;
        }
        $rules->addRule(Form::FILLED, _('Příjmení je povinné.'));



        if ($options & self::SHOW_DISPLAY_NAME) {
            $control = $this->createDisplayName()
                    ->setDisabled($disabled);
            $container->addComponent($control, 'display_name');
        }



        if ($options & self::SHOW_GENDER) {
            $control = $this->createGender()
                    ->setDefaultValue('M')
                    ->setDisabled($disabled);
            $container->addComponent($control, 'gender');
        }

        return $container;
    }

    public function createPersonInfo($options = 0, ControlGroup $group = null, $emailRule = null) {
        $container = new PersonInfoContainer();
        $container->setCurrentGroup($group);

        if (!($options & self::SHOW_LIKE_SUPPLEMENT)) {
            if ($options & self::SHOW_EMAIL) {
                $this->appendEmailWithLogin($container, $emailRule, $options);
            }

            $control = $this->createBorn();
            $container->addComponent($control, 'born');

            $control = $this->createIdNumber();
            $container->addComponent($control, 'id_number');

            $control = $this->createBornId();
            $container->addComponent($control, 'born_id');

            $control = $this->createPhone();
            $container->addComponent($control, 'phone');

            $control = $this->createIm();
            $container->addComponent($control, 'im');

            $control = $this->createBirthplace();
            $container->addComponent($control, 'birthplace');


            if ($options & self::SHOW_ORG_INFO) {
                $control = $this->createUkLogin();
                $container->addComponent($control, 'uk_login');

                $control = $this->createAccount();
                $container->addComponent($control, 'account');

                $control = $this->createTexSignature();
                $container->addComponent($control, 'tex_signature');

                $control = $this->createDomainAlias();
                $container->addComponent($control, 'domain_alias');

                $control = $this->createCareer();
                $container->addComponent($control, 'career');

                $control = $this->createHomepage();
                $container->addComponent($control, 'homepage');
            }

            $control = $this->createNote();
            $container->addComponent($control, 'note');
        }
        $control = $this->createOrigin();
        $container->addComponent($control, 'origin');

        $control = $this->createAgreed();
        $container->addComponent($control, 'agreed');

        if ($options & self::REQUIRE_AGREEMENT) {
            $control->addRule(Form::FILLED, _('Bez souhlasu nelze bohužel pokračovat.'));
        }

        return $container;
    }

    public function appendEmailWithLogin(Container $container, callable $emailRule = null, $options = 0) {
        $emailElement = $this->createEmail();
        $container->addComponent($emailElement, 'email');

        if ($options & self::REQUIRE_EMAIL) {
            $emailElement->addRule(Form::FILLED, _('E-mail je třeba zadat.'));
        }

        $filledEmailCondition = $emailElement->addCondition(Form::FILLED);
        if ($emailRule) {
            $filledEmailCondition->addRule($emailRule, _('Daný e-mail je již použit u někoho jiného.'));
        }

        if ($options & self::SHOW_LOGIN_CREATION) {
            $loginContainer = $container->addContainer(self::CONT_LOGIN);

            $createLogin = $loginContainer->addCheckbox(self::EL_CREATE_LOGIN, _('Vytvořit login'))
                    ->setDefaultValue(true)
                    ->setOption('description', _('Vytvoří login a pošle e-mail s instrukcemi pro první přihlášení.'));
            $emailElement->addConditionOn($createLogin, Form::FILLED)
                    ->addRule(Form::FILLED, _('Pro vytvoření loginu je třeba zadat e-mail.'));

            $langElement = $loginContainer->addSelect(self::EL_CREATE_LOGIN_LANG, _('Jazyk pozvánky'))
                    ->setItems($this->translator->getSupportedLanguages(), false)
                    ->setDefaultValue($this->translator->getLang());

            $createLogin->addCondition(Form::EQUAL, true);
            //TODO support in Nette         ->toggle($langElement->getHtmlId() . BootstrapRenderer::PAIR_ID_SUFFIX, true);
            //TODO support in Nette $filledEmailCondition->toggle($createLogin->getHtmlId() . BootstrapRenderer::PAIR_ID_SUFFIX, true);
        }
    }

    public function modifyLoginContainer(Container $container, ModelPerson $person) {

        $login = $person->getLogin();
        $personInfo = $person->getInfo();
        $hasEmail = $personInfo && isset($personInfo->email);
        $showLogin = !$login || !$hasEmail;

        //$container = $form[self::CONT_PERSON][PersonFactory::CONT_LOGIN];
        $loginContainer = $container[self::CONT_LOGIN];
        if (!$showLogin) {
            foreach ($loginContainer->getControls() as $control) {
                $control->setDisabled();
            }
        }
        if ($login) {
            $loginContainer[PersonFactory::EL_CREATE_LOGIN]->setDefaultValue(true);
            $loginContainer[PersonFactory::EL_CREATE_LOGIN]->setDisabled();
            $loginContainer[PersonFactory::EL_CREATE_LOGIN]->setOption('description', _('Login už existuje.'));
        }

        //$emailElement = $form[self::CONT_PERSON]['email'];
        $emailElement = $container['email'];
        $email = ($personInfo && isset($personInfo->email)) ? $personInfo->email : null;
        $emailElement->setDefaultValue($email);


        $emailRule = $this->uniqueEmailFactory->create($person);
        $emailElement->addRule($emailRule, _('Daný e-mail již někdo používá.'));
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

    public function createPersonHistory($options = 0, ControlGroup $group = null, $acYear) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        if ($options & self::REQUIRE_SCHOOL) {
            $school = $this->createSchoolId($acYear);
            $school->addRule(Form::FILLED, _('Je třeba zadat školu.'));
        } else {
            $school = $this->factorySchool->createSchoolSelect();
        }
        $container->addComponent($school, 'school_id');

        $studyYear = $this->createStudyYear($acYear);
        $container->addComponent($studyYear, 'study_year');

        if ($options & self::REQUIRE_STUDY_YEAR) {
            $studyYear->addRule(Form::FILLED, _('Je třeba zadat ročník.'));
        }


        $control = $this->createClass()
                ->setOption('description', _('Kvůli případné školní korespondenci.'));
        $container->addComponent($control, 'class');

        return $container;
    }

    public function createField($sub, $fieldName, $acYear, HiddenField $hiddenField = null, $metadata = array()) {
        if ($sub == 'post_contact') {
            if ($fieldName == 'type') {
                $required = Arrays::get($metadata, 'required', false);
                return new HiddenField($required);
            } else if ($fieldName == 'address') {
                //TODO required, support for multiple addresses?
                //TODO insert into group
                return $this->addressFactory->createAddress();
            }
        } else {
            $methodName = 'create' . str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));
            $control = call_user_func(array($this, $methodName), $acYear);

            if (Arrays::get($metadata, 'required', false)) {
                $conditioned = $control;
                if ($hiddenField) {
                    $conditioned = $control->addConditionOn($hiddenField, Form::FILLED);
                }
                $conditioned->addRule(Form::FILLED, _('Pole %label je povinné.'));
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
        return (new DatePicker(_('Datum narození')));
    }

    public function createIdNumber($acYear = null) {
        return (new TextInput(_('Číslo OP')))
                        ->setOption('description', _('U cizinců číslo pasu.'))
                        ->addRule(Form::MAX_LENGTH, null, 32);
    }

    public function createBornId($acYear = null) {
        $control = new TextInput(_('Rodné číslo'));
        $control->setOption('description', _('U cizinců prázdné.'))
                ->addCondition(Form::FILLED)
                ->addRule(new BornNumber(), _('Rodné číslo nemá platný formát.'));
        return $control;
    }

    public function createPhone($acYear = null) {
        return (new TextInput(_('Telefonní číslo')))
                        ->addRule(Form::MAX_LENGTH, null, 32);
    }

    public function createIm($acYear = null) {
        return (new TextInput(_('ICQ, Jabber, apod.')))
                        ->addRule(Form::MAX_LENGTH, null, 32);
    }

    public function createBirthplace($acYear = null) {
        return (new TextInput(_('Místo narození')))
                        ->setOption('description', _('Město a okres (kvůli diplomům).'))
                        ->addRule(Form::MAX_LENGTH, null, 255);
    }

    public function createUkLogin($acYear = null) {
        return (new TextInput(_('Login UK')))
                        ->addRule(Form::MAX_LENGTH, null, 8);
    }

    public function createAccount($acYear = null) {
        return (new TextInput(_('Číslo bankovního účtu')))
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

    /*
     * Person history
     */

    public function createStudyYear($acYear) {
        $studyYear = new SelectBox(_('Ročník'));

        $hsYears = array();
        foreach (range(1, 4) as $study_year) {
            $hsYears[$study_year] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'), $study_year, $acYear + (5 - $study_year));
        }

        $primaryYears = array();
        foreach (range(6, 9) as $study_year) {
            $primaryYears[$study_year] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'), $study_year, $acYear + (5 - ($study_year - 9)));
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

