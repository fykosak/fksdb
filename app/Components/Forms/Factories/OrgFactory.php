<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\ModelContainer;
use Nette\Forms\Form;
use ServicePerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class OrgFactory {

    const SHOW_PERSON = 0x1;

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    function __construct(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    public function createContestant($options = 0, ControlGroup $group = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        if ($options & self::SHOW_PERSON) {
            $persons = $this->servicePerson->getTable()->order('family_name, other_name');
            $items = array();
            while ($person = $persons->fetch()) {
                $items[$person->person_id] = $person->getFullname();
            }

            //TODO komponenta výběru osoby
            $container->addSelect('person_id', 'Osoba')
                    ->setItems($items);
        }


        //TODO validate range
        $container->addText('since', 'Od ročníku')
                ->addRule(Form::NUMERIC)
                ->addRule(Form::FILLED);

        $container->addText('until', 'Do ročníku')
                ->addRule(Form::NUMERIC);


        $container->addText('role', 'Funkce')
                ->addRule(Form::MAX_LENGTH, null, 32);

        $container->addText('order', 'Hodnost')
                ->setOption('description', 'Pro řazení v seznamu organizátorů')
                ->addRule(Form::NUMERIC)
                ->addRule(Form::FILLED);

        $container->addText('tex_signature', 'Podpis v TeXu')
                ->addRule(Form::FILLED);

        $container->addTextArea('note', 'Poznámka');

        return $container;
    }

}
