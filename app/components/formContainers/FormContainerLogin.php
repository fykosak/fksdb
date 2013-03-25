<?php

use \Nette\Forms\Form;

/**
 * TODO nefunguje kontrola shody hesel!!
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

        $this->addText('email', 'E-mail')
                ->addRule(Form::EMAIL, 'Neplatný tvar e-mailu.')
                ->addRule(Form::FILLED);

        switch ($pwdMode) {
            case self::PWD_CHANGE_VERIFIED:
                $this->addPassword('old_password', 'Staré heslo');

                $newPwd = $this->addPassword('password', 'Nové heslo')
                        ->addConditionOn($this['old_password'], Form::FILLED)
                        ->addRule(Form::FILLED, "Je třeba nastavit nové heslo.");

                $this->addPassword('password_verify', 'Nové heslo ověření')
                        ->addCondition(Form::EQUAL, 'Zadaná hesla se neshodují.', $this['password']);
                break;
            case self::PWD_NEW_VERIFIED:
                $newPwd = $this->addPassword('password', 'Heslo')
                        ->addRule(Form::FILLED, 'Heslo je povinné.');

                $this->addPassword('password_verify', 'Heslo ověření')
                        ->addCondition(Form::EQUAL, 'Zadaná hesla se neshodují.', $this['password']);

                break;
            case self::PWD_UNVERIFIED:
                $this->addPassword('password', 'Heslo');
                break;
        }
    }

}
