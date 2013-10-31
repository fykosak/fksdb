<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use Nette\Application\UI\Form;
use Nette\Forms\ControlGroup;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class LoginFactory {

    const SHOW_ACTIVE = 0x1;
    /** show field pair for setting a password */
    const SHOW_PASSWORD = 0x2;
    /** show field for the old password */
    const VERIFY_OLD_PASSWORD = 0x4;
    /** require nonempty (new) password */
    const REQUIRE_PASSWORD = 0x8;

    /**
     * @param type $options
     */
    public function createLogin($options = 0, ControlGroup $group = null, $emailRule = null, $loginRule = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $login = $container->addText('login', _('Přihlašovací jméno'));

        if ($loginRule) {
            $login->addRule($loginRule, _('Daný login již někdo používá.'));
        }

        $email = $container->addText('email', _('E-mail'))
                ->addRule(Form::EMAIL, _('Neplatný tvar e-mailu.'))
                ->addRule(Form::FILLED, _('E-mail je třeba zadat.'));

        if ($emailRule) {
            $email->addRule($emailRule, _('Daný email již někdo používá.'));
        }

        if ($options & self::SHOW_PASSWORD) {
            if ($options & self::VERIFY_OLD_PASSWORD) {
                $container->addPassword('old_password', _('Staré heslo'));
            }
            $newPwd = $container->addPassword('password', _('Heslo'));
            $newPwd->addCondition(Form::FILLED)->addRule(Form::MIN_LENGTH, _('Heslo musí mít alespoň %d znaků.'), 6);

            if ($options & self::VERIFY_OLD_PASSWORD) {
                $newPwd->addConditionOn($container['old_password'], Form::FILLED)
                        ->addRule(Form::FILLED, _("Je třeba nastavit nové heslo."));
            } else if ($options & self::REQUIRE_PASSWORD) {
                $newPwd->addRule(Form::FILLED, _("Heslo nemůže být prázdné."));
            }


            $container->addPassword('password_verify', _('Heslo (ověření)'))
                    ->addRule(Form::EQUAL, _('Zadaná hesla se neshodují.'), $newPwd);
        }

        if ($options & self::SHOW_ACTIVE) {
            $container->addCheckbox('active', _('Aktivní účet'));
        }

        return $container;
    }

}
