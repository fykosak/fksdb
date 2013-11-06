<?php

namespace FKSDB\Components\Forms\Factories;

use FKS\Components\Forms\Controls\URLTextBox;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\PersonInfoContainer;
use FKSDB\Components\Forms\Rules\BornNumber;
use Nette\Forms\ControlGroup;
use Nette\Forms\Form;
use Nette\Utils\Html;

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


    /* Encapsulation condition argument (workaround) */
    const IDX_CONTROL = 'control';
    const IDX_OPERATION = 'op';
    const IDX_VALUE = 'val';

    public function createPerson($options = 0, ControlGroup $group = null, array $requiredCondition = null) {
        $disabled = (bool) ($options & self::DISABLED);

        $container = new ModelContainer();
        $container->setCurrentGroup($group);


        $control = $container->addText('other_name', _('Křestní jméno'))
                ->setDisabled($disabled)
                ->setOption('description', _('Příp. další jména oddělená mezerou.'));

        if ($requiredCondition) {
            $rules = $control->addConditionOn($requiredCondition[self::IDX_CONTROL], $requiredCondition[self::IDX_OPERATION], $requiredCondition[self::IDX_VALUE]);
        } else {
            $rules = $control;
        }
        $rules->addRule(Form::FILLED, _('Křestní jméno je povinné.'));



        $control = $container->addText('family_name', _('Příjmení'))
                ->setDisabled($disabled)
                ->setOption('description', _('Příp. další jména oddělená mezerou.'));

        if ($requiredCondition) {
            $rules = $control->addConditionOn($requiredCondition[self::IDX_CONTROL], $requiredCondition[self::IDX_OPERATION], $requiredCondition[self::IDX_VALUE]);
        } else {
            $rules = $control;
        }
        $rules->addRule(Form::FILLED, _('Příjmení je povinné.'));



        if ($options & self::SHOW_DISPLAY_NAME) {
            $control = $container->addText('display_name', _('Zobrazované jméno'))
                    ->setDisabled($disabled)
                    ->setOption('description', _('Pouze pokud je odlišené od "jméno příjmení".'));
        }



        if ($options & self::SHOW_GENDER) {
            $control = $container->addRadioList('gender', _('Pohlaví'), array('M' => 'muž', 'F' => 'žena'))
                    ->setDefaultValue('M')
                    ->setDisabled($disabled);
        }

        return $container;
    }

    public function createPersonInfo($options = 0, ControlGroup $group = null, $emailRule = null) {
        $container = new PersonInfoContainer();
        $container->setCurrentGroup($group);

        if (!($options & self::SHOW_LIKE_SUPPLEMENT)) {
            if ($options & self::SHOW_EMAIL) {
                $email = $container->addText('email', _('E-mail'));
                $conditioned = $email->addCondition(Form::FILLED)
                        ->addRule(Form::EMAIL, _('Neplatný tvar e-mailu.'));
                if ($emailRule) {
                    $conditioned->addRule($emailRule, _('Daný e-mail je již použit u někoho jiného.'));
                }
            }

            $container->addDatePicker('born', 'Datum narození');

            $container->addText('id_number', _('Číslo OP'))
                    ->setOption('description', _('U cizinců číslo pasu.'))
                    ->addRule(Form::MAX_LENGTH, null, 32);

            $container->addText('born_id', _('Rodné číslo'))
                    ->setOption('description', _('U cizinců prázdné.'))
                    ->addCondition(Form::FILLED)
                    ->addRule(new BornNumber(), _('Rodné číslo nemá platný formát.'));


            $container->addText('phone', _('Telefonní číslo'))
                    ->addRule(Form::MAX_LENGTH, null, 32);

            $container->addText('im', _('ICQ, Jabber, apod.'))
                    ->addRule(Form::MAX_LENGTH, null, 32);

            $container->addText('birthplace', _('Místo narození'))
                    ->setOption('description', _('Město a okres (kvůli diplomům).'))
                    ->addRule(Form::MAX_LENGTH, null, 255);



            if ($options & self::SHOW_ORG_INFO) {
                $container->addText('uk_login', _('Login UK'))
                        ->addRule(Form::MAX_LENGTH, null, 8);

                $container->addText('account', _('Číslo bankovního účtu'))
                        ->addRule(Form::MAX_LENGTH, null, 32);

                $container->addText('tex_signature', _('TeX identifikátor'))
                        ->addRule(Form::MAX_LENGTH, null, 32)
                        ->addCondition(Form::FILLED)
                        ->addRule(Form::REGEXP, _('%label obsahuje nepovolené znaky.'), '/^[a-z][a-z0-9._\-]*$/i');

                $container->addText('domain_alias', _('Jméno v doméně fykos.cz'))
                        ->addRule(Form::MAX_LENGTH, null, 32)
                        ->addCondition(Form::FILLED)
                        ->addRule(Form::REGEXP, _('%l obsahuje nepovolené znaky.'), '/^[a-z][a-z0-9._\-]*$/i');

                $container->addTextArea('career', _('Co právě dělá'))
                        ->setOption('description', _('Zobrazeno v síni slávy'));

                $url = new URLTextBox(_('Homepage'));
                $url->addRule(Form::MAX_LENGTH, null, 255);
                $container->addComponent($url, 'homepage');
            }

            $container->addTextArea('note', _('Poznámka'));
        }
        $container->addTextArea('origin', _('Jak jsi se o nás dozvěděl(a)?'));


        $link = Html::el('a');
        $link->setText(_('Text souhlasu'));
        $link->href = _("http://fykos.cz/doc/souhlas.pdf");

        $agreement = $container->addCheckbox('agreed', _('Souhlasím se zpracováním osobních údajů'))
                ->setOption('description', $link);

        if ($options & self::REQUIRE_AGREEMENT) {
            $agreement->addRule(Form::FILLED, _('Bez souhlasu nelze bohužel pokračovat.'));
        }


        return $container;
    }

}
