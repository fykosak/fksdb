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

        $login = $container->addText('login', 'Přihlašovací jméno');

        if ($loginRule) {
            $login->addRule($loginRule, 'Daný login již někdo používá.');
        }

        $email = $container->addText('email', 'E-mail')
                ->addRule(Form::EMAIL, 'Neplatný tvar e-mailu.')
                ->addRule(Form::FILLED, 'E-mail je třeba zadat.');

        if ($emailRule) {
            $email->addRule($emailRule, 'Daný email již někdo používá.');
        }

        if ($options & self::SHOW_PASSWORD) {
            if ($options & self::VERIFY_OLD_PASSWORD) {
                $container->addPassword('old_password', 'Staré heslo');
            }
            $newPwd = $container->addPassword('password', 'Heslo');
            $newPwd->addCondition(Form::FILLED)->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků.', 6);

            if ($options & self::VERIFY_OLD_PASSWORD) {
                $newPwd->addConditionOn($container['old_password'], Form::FILLED)
                        ->addRule(Form::FILLED, "Je třeba nastavit nové heslo.");
            } else if ($options & self::REQUIRE_PASSWORD) {
                $newPwd->addRule(Form::FILLED, "Heslo nemůže být prázdné.");
            }


            $container->addPassword('password_verify', 'Heslo (ověření)')
                    ->addRule(Form::EQUAL, 'Zadaná hesla se neshodují.', $newPwd);
        }

        if ($options & self::SHOW_ACTIVE) {
            $container->addCheckbox('active', 'Aktivní účet');
        }

        return $container;
    }

}
