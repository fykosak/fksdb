<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\DateInputs\DateTimeLocalInput;
use FKSDB\ORM\ModelContest;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;
use ServiceEventType;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EventFactory {

    const SHOW_UNKNOWN_SCHOOL_HINT = 0x1;

    /**
     * @var ServiceEventType
     */
    private $serviceEventType;

    function __construct(ServiceEventType $serviceEventType) {
        $this->serviceEventType = $serviceEventType;
    }

    /**
     * @param ModelContest $contest
     * @param int $options
     * @param ControlGroup|null $group
     * @return ModelContainer
     */
    public function createEvent(ModelContest $contest, $options = 0, ControlGroup $group = null): ModelContainer {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $type = $this->createEventType($contest);
        $type->addRule(Form::FILLED, _('%label je povinný.'));

        $container->addComponent($type, 'event_type_id');

        $container->addText('event_year', _('Ročník akce'))
            ->addRule(Form::INTEGER, _('%label musí být číslo.'))
            ->addRule(Form::FILLED, _('%label je povinný.'))
            ->setOption('description', _('Ročník akce musí být unikátní pro daný typ akce.'));

        $container->addText('name', _('Název'))
            ->addRule(Form::FILLED, _('%label je povinný.'))
            ->addRule(Form::MAX_LENGTH, null, 255)
            ->setOption('description', _('U soustředka místo.'));

        $control = new DateTimeLocalInput(_('Začátek akce'));
        $control->addRule(Form::FILLED, _('%label je povinný.'));
        $container->addComponent($control, 'begin');

        $control = new DateTimeLocalInput(_('Konec akce'));
        $control->addRule(Form::FILLED, _('%label je povinný.'))
            ->setOption('description', _('U jednodenních akcí shodný se začátkem.'));
        $container->addComponent($control, 'end');;

        $control = new DateTimeLocalInput(_('Začátek registrace'));
        $container->addComponent($control, 'registration_begin');

        $control = new DateTimeLocalInput(_('Konec registrace'));
        $container->addComponent($control, 'registration_end');


        $container->addTextArea('report', _('Text'))
            ->setOption('description', _('Shrnující text k akci.'));

        $container->addTextArea('parameters', _('Parametry'))
            ->setOption('description', _('V Neon syntaxi, schéma je specifické pro definici akce.'));

        return $container;
    }

    public function createEventType(ModelContest $contest): SelectBox {
        $element = new SelectBox(_('Typ akce'));

        $types = $this->serviceEventType->getTable()->where('contest_id', $contest->contest_id)->fetchPairs('event_type_id', 'name');
        $element->setItems($types);
        $element->setPrompt(_('Zvolit typ'));

        return $element;
    }

}
