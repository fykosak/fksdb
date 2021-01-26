<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\SchoolProvider;
use Nette\Forms\Form;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SchoolFactory {

    private const SHOW_UNKNOWN_SCHOOL_HINT = 0x1;

    private SchoolProvider $schoolProvider;

    public function __construct(SchoolProvider $schoolProvider) {
        $this->schoolProvider = $schoolProvider;
    }

    public function createContainer(): ModelContainer {
        $container = new ModelContainer();
        $container->addText('name_full', _('Full name'))
            ->addRule(Form::MAX_LENGTH, null, 255)
            ->setOption('description', _('Full name of the school.'));

        $container->addText('name', _('Name'))
            ->addRule(Form::MAX_LENGTH, null, 255)
            ->addRule(Form::FILLED, _('Name is required.'))
            ->setOption('description', _('Envelope name.'));

        $container->addText('name_abbrev', _('Abbreviated name'))
            ->addRule(Form::MAX_LENGTH, _('The length of the abbreviated name is restricted to a maximum %d characters.'), 32)
            ->addRule(Form::FILLED, _('Short name is required.'))
            ->setOption('description', _('Very short name.'));

        $container->addText('email', _('Contact e-mail'))
            ->addCondition(Form::FILLED)
            ->addRule(Form::EMAIL);

        $container->addText('ic', _('IČ'))
            ->addRule(Form::MAX_LENGTH, _('Délka IČ je omezena na %d znaků.'), 8);

        $container->addText('izo', _('IZO'))
            ->addRule(Form::MAX_LENGTH, _('Délka IZO je omezena na %d znaků.'), 32);

        $container->addCheckbox('active', _('Active record'))
            ->setDefaultValue(true);

        $container->addText('note', _('Note'));

        return $container;
    }

    public function createSchoolSelect(int $options = 0): AutocompleteSelectBox {
        $schoolElement = new AutocompleteSelectBox(true, _('School'));
        $schoolElement->setDataProvider($this->schoolProvider);
        if ($options & self::SHOW_UNKNOWN_SCHOOL_HINT) {
            $schoolElement->setOption('description', sprintf(_('If you cannot find the school, ask on e-mail %s.'), 'schola.novum () fykos.cz'));
        }
        return $schoolElement;
    }
}
