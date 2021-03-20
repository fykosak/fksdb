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

    public function createLogin(int $options = 0, ?ControlGroup $group = null, ?callable $loginRule = null): ModelContainer {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $login = $container->addText('login', _('Username'));
        $login->setHtmlAttribute('autocomplete', 'username');

        if ($loginRule) {
            $login->addRule($loginRule, _('This username is already taken.'));
        }

        if ($options & self::SHOW_PASSWORD) {
            if ($options & self::VERIFY_OLD_PASSWORD) {
                $container->addPassword('old_password', _('Old password'))->setHtmlAttribute('autocomplete', 'current-password');
            }
            $newPwd = $container->addPassword('password', _('Password'));
            $newPwd->setHtmlAttribute('autocomplete', 'new-password');
            $newPwd->addCondition(Form::FILLED)->addRule(Form::MIN_LENGTH, _('The password must have at least %d characters.'), 6);

            if ($options & self::VERIFY_OLD_PASSWORD) {
                $newPwd->addConditionOn($container->getComponent('old_password'), Form::FILLED)
                    ->addRule(Form::FILLED, _('It is necessary to set a new password.'));
            } elseif ($options & self::REQUIRE_PASSWORD) {
                $newPwd->addRule(Form::FILLED, _('Password cannot be empty.'));
            }


            $container->addPassword('password_verify', _('Password (verification)'))
                ->addRule(Form::EQUAL, _('The submitted passwords do not match.'), $newPwd)
                ->setHtmlAttribute('autocomplete', 'new-password');
        }

        return $container;
    }
}
