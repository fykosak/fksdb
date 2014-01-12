<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use Nette\Forms\ControlGroup;
use Nette\Forms\Form;
use ServiceContest;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ContestantFactory {
    //TODO remove this option as it's effectively useless

    const SHOW_CONTEST = 0x1;

    /**
     * @var ServiceContest
     */
    private $serviceContest;

    function __construct(ServiceContest $serviceContest) {
        $this->serviceContest = $serviceContest;
    }

    /**
     * 
     * @deprecated Use person_history instead.
     * @param type $options
     * @param ControlGroup $group
     * @return ModelContainer
     */
    public function createContestant($options = 0, ControlGroup $group = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        if ($options & self::SHOW_CONTEST) {
            $container->addSelect('contest_id', _('Seminář'))
                    ->setItems($this->serviceContest->getTable()->fetchPairs('contest_id', 'name'))
                    ->setPrompt(_('Zvolit seminář'))
                    ->addRule(Form::FILLED, _('Je třeba zvolit seminář.'));
        }


        return $container;
    }

}
