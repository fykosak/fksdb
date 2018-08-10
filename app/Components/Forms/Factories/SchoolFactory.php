<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\SchoolProvider;
use FKSDB\Components\Forms\Factories\PersonHistory\SchoolIdSelectField;
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
     * @param int $options
     * @param ControlGroup|null $group
     * @return ModelContainer
     */
    public function createSchool($options = 0, ControlGroup $group = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $container->addText('name_full', _('Plný název'))
            ->addRule(Form::MAX_LENGTH, null, 255)
            ->setOption('description', _('Úplný nezkrácený název školy.'));

        $container->addText('name', _('Název'))
            ->addRule(Form::MAX_LENGTH, null, 255)
            ->addRule(Form::FILLED, _('Název je povinný.'))
            ->setOption('description', _('Název na obálku.'));

        $container->addText('name_abbrev', _('Zkrácený název'))
            ->addRule(Form::MAX_LENGTH, _('Délka zkráceného názvu je omezena na %d znaků.'), 32)
            ->addRule(Form::FILLED, _('Zkrácený název je povinný.'))
            ->setOption('description', _('Název krátký do výsledkovky.'));

        $container->addText('email', _('Kontaktní e-mail'))
            ->addCondition(Form::FILLED)
            ->addRule(Form::EMAIL);

        $container->addText('ic', _('IČ'))
            ->addRule(Form::MAX_LENGTH, _('Délka IČ je omezena na %d znaků.'), 8);

        $container->addText('izo', _('IZO'))
            ->addRule(Form::MAX_LENGTH, _('Délka IZO je omezena na %d znaků.'), 32);

        $container->addCheckbox('active', _('Aktivní záznam'))
            ->setDefaultValue(true);

        $container->addText('note', _('Poznámka'));

        //$container->addHidden('school_id');

        return $container;
    }

    public function createSchoolSelect() {
        return new SchoolIdSelectField($this->schoolProvider);
    }

}
