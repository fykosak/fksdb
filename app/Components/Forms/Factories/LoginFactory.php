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

    public const SHOW_ACTIVE = 0x1;
    /** show field pair for setting a password */
    public const SHOW_PASSWORD = 0x2;
    /** show field for the old password */
    public const VERIFY_OLD_PASSWORD = 0x4;
    /** require nonempty (new) password */
    public const REQUIRE_PASSWORD = 0x8;

    /**
     * @param int $options
     * @param ControlGroup|null $group
     * @param null $loginRule
     * @return ModelContainer
     */
    public function createLogin($options = 0, ControlGroup $group = null, $loginRule = null): ModelContainer {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $login = $container->addText('login', _('Přihlašovací jméno'));
        $login->setAttribute('autocomplete', 'username');

        if ($loginRule) {
            $login->addRule($loginRule, _('Daný login již někdo používá.'));
        }

        if ($options & self::SHOW_PASSWORD) {
            if ($options & self::VERIFY_OLD_PASSWORD) {
                $container->addPassword('old_password', _('Staré heslo'))->setAttribute('autocomplete', 'current-password');
            }
            $newPwd = $container->addPassword('password', _('Heslo'));
            $newPwd->setAttribute('autocomplete', 'new-password');
            $newPwd->addCondition(Form::FILLED)->addRule(Form::MIN_LENGTH, _('Heslo musí mít alespoň %d znaků.'), 6);

            if ($options & self::VERIFY_OLD_PASSWORD) {
                $newPwd->addConditionOn($container['old_password'], Form::FILLED)
                    ->addRule(Form::FILLED, _('Je třeba nastavit nové heslo.'));
            } elseif ($options & self::REQUIRE_PASSWORD) {
                $newPwd->addRule(Form::FILLED, _('Heslo nemůže být prázdné.'));
            }


            $container->addPassword('password_verify', _('Heslo (ověření)'))
                ->addRule(Form::EQUAL, _('Zadaná hesla se neshodují.'), $newPwd)
                ->setAttribute('autocomplete', 'new-password');
        }

        return $container;
    }

}
