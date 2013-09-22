<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Rules\BornNumber;
use Nette\Forms\ControlGroup;
use Nette\Forms\Form;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonFactory {

    const SHOW_DISPLAY_NAME = 0x1; // for person
    const SHOW_ORG_INFO = 0x2; // for person info
    const SHOW_AGREEMENT = 0x4; // for person info
    const SHOW_EMAIL = 0x8; // for person info
    const SHOW_GENDER = 0x10; // for person
    const SHOW_LOGIN_CREATION = 0x20; // for person info
    const DISABLED = 0x20; // for person

    /* Important elements */
    const EL_CREATE_LOGIN = 'createLogin';

    /* Encapsulation condition argument (workaround) */
    const IDX_CONTROL = 'control';
    const IDX_OPERATION = 'op';
    const IDX_VALUE = 'val';

    public function createPerson($options = 0, ControlGroup $group = null, array $requiredCondition = null) {
        $disabled = (bool) ($options & self::DISABLED);

        $container = new ModelContainer();
        $container->setCurrentGroup($group);


        $control = $container->addText('other_name', 'Křestní jméno')
                ->setDisabled($disabled)
                ->setOption('description', 'Příp. další jména oddělaná mezerou.');

        if ($requiredCondition) {
            $rules = $control->addConditionOn($requiredCondition[self::IDX_CONTROL], $requiredCondition[self::IDX_OPERATION], $requiredCondition[self::IDX_VALUE]);
        } else {
            $rules = $control;
        }
        $rules->addRule(Form::FILLED, 'Křestní jméno je povinné.');



        $control = $container->addText('family_name', 'Příjmení')
                ->setDisabled($disabled)
                ->setOption('description', 'Příp. další jména oddělaná mezerou.');

        if ($requiredCondition) {
            $rules = $control->addConditionOn($requiredCondition[self::IDX_CONTROL], $requiredCondition[self::IDX_OPERATION], $requiredCondition[self::IDX_VALUE]);
        } else {
            $rules = $control;
        }
        $rules->addRule(Form::FILLED, 'Příjmení je povinné.');



        if ($options & self::SHOW_DISPLAY_NAME) {
            $control = $container->addText('display_name', 'Zobrazované jméno')
                    ->setDisabled($disabled)
                    ->setOption('description', 'Pouze pokud je odlišené od "jméno příjmení".');
        }



        if ($options & self::SHOW_GENDER) {
            $control = $container->addRadioList('gender', 'Pohlaví', array('M' => 'muž', 'F' => 'žena'))
                    ->setDefaultValue('M')
                    ->setDisabled($disabled);
        }

        return $container;
    }

    public function createPersonInfo($options = 0, ControlGroup $group = null, $emailRule = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        if ($options & self::SHOW_EMAIL) {
            $email = $container->addText('email', 'E-mail');
            $conditioned = $email->addCondition(Form::FILLED)
                    ->addRule(Form::EMAIL, 'Neplatný tvar e-mailu.');
            if ($emailRule) {
                $conditioned->addRule($emailRule, 'Daný e-mail je již použit u někoho jiného.');
            }
            if ($options & self::SHOW_LOGIN_CREATION) {
                $createLogin = $container->addCheckbox(self::EL_CREATE_LOGIN, 'Vytvořit login')
                        ->setOption('description', 'Vytvoří login a pošle e-mail s instrukcemi pro první přihlášení.');
                $email->addConditionOn($createLogin, Form::FILLED)
                        ->addRule(Form::FILLED, 'Pro vytvoření loginu je třeba zadat e-mail.');
            }
        }

        $container->addDatePicker('born', 'Datum narození');

        $container->addText('id_number', 'Číslo OP')
                ->setOption('description', 'U cizinců číslo pasu.')
                ->addRule(Form::MAX_LENGTH, null, 32);

        $container->addText('born_id', 'Rodné číslo')
                ->setOption('description', 'U cizinců prázdné.')
                ->addCondition(Form::FILLED)
                ->addRule(new BornNumber(), 'Rodné číslo nemá platný formát.');


        $container->addText('phone', 'Telefonní číslo')
                ->addRule(Form::MAX_LENGTH, null, 32);

        $container->addText('im', 'ICQ, Jabber, apod.')
                ->addRule(Form::MAX_LENGTH, null, 32);

        $container->addText('birthplace', 'Místo narození')
                ->setOption('description', 'Město a okres (kvůli diplomům).')
                ->addRule(Form::MAX_LENGTH, null, 255);



        if ($options & self::SHOW_ORG_INFO) {
            $container->addText('uk_login', 'Login UK')
                    ->addRule(Form::MAX_LENGTH, null, 8);

            $container->addText('account', 'Číslo bankovního účtu')
                    ->addRule(Form::MAX_LENGTH, null, 32);
        }

        $container->addTextArea('note', 'Poznámka');

        if ($options & self::SHOW_AGREEMENT) {
//TODO odkaz na souhlas
            $container->addCheckbox('agree', 'Souhlasím se zpracováním osobních údajů')
                    ->setOption('description', 'ODKAZ na souhlas.');
        }

        return $container;
    }

}
