<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\SchoolProvider;
use Nette\Forms\Form;

class SchoolFactory
{

    private SchoolProvider $schoolProvider;

    public function __construct(SchoolProvider $schoolProvider)
    {
        $this->schoolProvider = $schoolProvider;
    }

    public function createContainer(): ModelContainer
    {
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

        $container->addText('ic', _('IČ (Czech schools only)'))
            ->addRule(Form::MAX_LENGTH, _('The length of IČ is restricted to %d characters.'), 8);

        $container->addText('izo', _('IZO (Czech schools only)'))
            ->addRule(Form::MAX_LENGTH, _('The length of IZO is restricted to %d characters.'), 32);

        $container->addCheckbox('active', _('Active record'))
            ->setDefaultValue(true);

        $container->addText('note', _('Note'));

        return $container;
    }

    public function createSchoolSelect(bool $showUnknownSchoolHint = false): AutocompleteSelectBox
    {
        $schoolElement = new AutocompleteSelectBox(true, _('School'));
        $schoolElement->setDataProvider($this->schoolProvider);
        if ($showUnknownSchoolHint) {
            $schoolElement->setOption('description', sprintf(_('If you cannot find the school, ask on e-mail %s.'), 'schola.novum () fykos.cz'));
        }
        return $schoolElement;
    }
}
