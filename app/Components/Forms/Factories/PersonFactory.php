<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\ModelContainer;
use Nette\Forms\ControlGroup;
use Nette\Forms\Form;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonFactory {

    const SHOW_DISPLAY_NAME = 0x1;
    const SHOW_ORG_INFO = 0x2;
    const SHOW_AGREEMENT = 0x4;
    const SHOW_EMAIL = 0x8;

    public function createPerson($options = 0, ControlGroup $group = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);
        
        $container->addText('other_name', 'Křestní jméno')
                ->setOption('description', 'Příp. další jména oddělaná mezerou.')
                ->addRule(Form::FILLED, 'Křestní jméno je povinné.');

        $container->addText('family_name', 'Příjmení')
                ->setOption('description', 'Příp. další jména oddělaná mezerou.')
                ->addRule(Form::FILLED, 'Příjmení je povinné.');

        if ($options & self::SHOW_DISPLAY_NAME) {
            $this->addText('display_name', 'Zobrazované jméno')
                    ->setOption('description', 'Pouze pokud je odlišené od "jméno příjmení".');
        }
        
        return $container;
    }
    

    public function createPersonInfo($options = 0) {
        $container = new ModelContainer();

        $container->addDatePicker('born', 'Datum narození');

        $container->addText('id_number', 'Číslo OP')
                ->setOption('description', 'U cizinců číslo pasu.')
                ->addRule(Form::MAX_LENGTH, null, 32);

        //TODO validace rodného čísla
        $container->addText('born_id', 'Rodné číslo')
                ->setOption('description', 'U cizinců prázdné.')
                ->addRule(Form::MAX_LENGTH, null, 32);

        $container->addText('phone', 'Telefonní číslo')
                ->addRule(Form::MAX_LENGTH, null, 32);

        $container->addText('im', 'ICQ, Jabber, apod.')
                ->addRule(Form::MAX_LENGTH, null, 32);

        $container->addText('birthplace', 'Místo narození')
                ->setOption('description', 'Město a okres (kvůli diplomům).')
                ->addRule(Form::MAX_LENGTH, null, 255);

        if ($options & self::SHOW_EMAIL) {
            $this->addText('email', 'E-mail')
                    ->addRule(Form::EMAIL, 'Neplatný tvar e-mailu.')
                    ->addRule(Form::FILLED);
        }

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
