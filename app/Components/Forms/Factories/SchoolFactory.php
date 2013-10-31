<?php

namespace FKSDB\Components\Forms\Factories;

use FKS\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\SchoolProvider;
use Nette\Forms\ControlGroup;
use Nette\Forms\Form;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class SchoolFactory {

    /**
     * @var SchoolProvider
     */
    private $schoolProvider;

    function __construct(SchoolProvider $schoolProvider) {
        $this->schoolProvider = $schoolProvider;
    }

    /**
     * @param type $options
     */
    public function createSchool($options = 0, ControlGroup $group = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $container->addText('name_full', _('Plný název'))
                ->setOption('description', _('Úplný nezkrácený název školy.'));

        $container->addText('name', _('Název'))
                ->addRule(Form::FILLED, _('Název je povinný.'))
                ->setOption('description', _('Název na obálku.'));

        $container->addText('name_abbrev', _('Zkrácený název'))
                ->addRule(Form::FILLED, _('Zkrácený název je povinný.'))
                ->setOption('description', _('Název krátký do výsledkovky.'));

        $container->addText('email', _('Kontaktní e-mail'))
                ->addCondition(Form::FILLED)
                ->addRule(Form::EMAIL);

        $container->addText('ic', _('IČ'))
                ->addRule(Form::MAX_LENGTH, _('Délka IČ je omezena na 8 znaků.'), 8);

        $container->addText('izo', _('IZO'))
                ->addRule(Form::MAX_LENGTH, _('Délka IZO je omezena na 32 znaků.'), 32);

        $container->addCheckbox('active', _('Aktivní záznam'))
                ->setDefaultValue(true);

        $container->addText('note', _('Poznámka'));

        //$container->addHidden('school_id');

        return $container;
    }

    public function createSchoolSelect() {
        $schoolElement = new AutocompleteSelectBox(true, _('Škola'));
        $schoolElement->setDataProvider($this->schoolProvider);
        return $schoolElement;
    }

}
