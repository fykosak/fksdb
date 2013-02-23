<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormContainerPerson extends FormContainerModel {

    public function __construct($displayName = false) {
        parent::__construct(null, null);


        $this->addText('other_name', 'Křestní jméno')
                ->setOption('description', 'Příp. další jména oddělaná mezerou.')
                ->addRule(\Nette\Forms\Form::FILLED, 'Křestní jméno je povinné.');

        $this->addText('family_name', 'Příjmení')
                ->setOption('description', 'Příp. další jména oddělaná mezerou.')
                ->addRule(\Nette\Forms\Form::FILLED, 'Příjmení je povinné.');

        if ($displayName) {
            $this->addText('display_name', 'Zobrazované jméno')
                    ->setOption('description', 'Pouze pokud je odlišené od "jméno příjmení".');
        }
    }

}
