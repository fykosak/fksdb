<?php

use \Nette\Forms\Form;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormContainerLogin extends FormContainerModel {

    const PWD_NONE = 0;
    const PWD_CHANGE_VERIFIED = 1;
    const PWD_NEW_VERIFIED = 2;
    const PWD_UNVERIFIED = 3;

    public function __construct($pwdMode = self::PWD_NONE) {
        parent::__construct(null, null);


        $this->addText('login', 'Přihlašovací jméno');

        $this->addText('email')
                ->addRule(Form::EMAIL, 'Neplatný tvar e-mailu.');

        switch ($pwdMode) {
            case self::PWD_CHANGE_VERIFIED:
                $this->addPassword('old_password', 'Staré heslo');

                $newPwd = $this->addPassword('password', 'Nové heslo');

                $this->addPassword('password_verify', 'Nové heslo ověření')
                        ->addCondition(Form::FILLED)->addCondition(Form::EQUAL, $newPwd);
                break;
            case self::PWD_NEW_VERIFIED:
                $newPwd = $this->addPassword('password', 'Heslo');

                $this->addPassword('password_verify', 'Heslo ověření')
                        ->addCondition(Form::FILLED)->addCondition(Form::EQUAL, $newPwd);
                break;
            case self::PWD_UNVERIFIED:
                $this->addPassword('password', 'Heslo');
                break;
        }
    }

}
