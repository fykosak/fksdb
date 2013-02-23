<?php

use \Nette\Forms\Form;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormContainerPersonInfo extends FormContainerModel {

    public function __construct($orgInfo = false, $agreement = true) {
        parent::__construct(null, null);

        $this->addDatePicker('born', 'Datum narození');

        $this->addText('id_number', 'Číslo OP')
                ->setOption('description', 'U cizinců číslo pasu.')
                ->addRule(Form::MAX_LENGTH, null, 32);

        //TODO validace rodného čísla
        $this->addText('born_id', 'Rodné číslo')
                ->setOption('description', 'U cizinců prázdné.')
                ->addRule(Form::MAX_LENGTH, null, 32);

        $this->addText('phone', 'Telefonní číslo')
                ->addRule(Form::MAX_LENGTH, null, 32);

        $this->addText('im', 'ICQ, Jabber, apod.')
                ->addRule(Form::MAX_LENGTH, null, 32);

        $this->addTextArea('note', 'Poznámka');

        if ($orgInfo) {
            $this->addText('uk_login', 'Login UK')
                    ->addRule(Form::MAX_LENGTH, null, 8);

            $this->addText('account', 'Číslo bankovního účtu')
                    ->addRule(Form::MAX_LENGTH, null, 32);
        }

        if ($agreement) {
            //TODO odkaz na souhlas
            $this->addCheckbox('agree', 'Souhlasím se zpracováním osobních údajů')
                    ->setOption('description', 'ODKAZ na souhlas.');
        }
    }

}
