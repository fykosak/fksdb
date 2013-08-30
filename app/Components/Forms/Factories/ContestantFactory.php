<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\ModelContainer;
use Nette\Forms\ControlGroup;
use Nette\Forms\Form;
use ServiceSchool;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ContestantFactory {

    const REQUIRE_SCHOOL = 0x1;
    const REQUIRE_STUDY_YEAR = 0x2;

    /**
     * @var ServiceSchool
     */
    private $serviceSchool;

    function __construct(ServiceSchool $serviceSchool) {
        $this->serviceSchool = $serviceSchool;
    }

    public function createContestant($options = 0, ControlGroup $group = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);


        $schools = $this->serviceSchool->getSchools()->fetchPairs('school_id', 'name_full');
        //TODO komponenta výběru školy + kooperace s validací
        $school = $container->addSelect('school_id', 'Škola')
                ->setItems($schools);

        if ($options & self::REQUIRE_SCHOOL) {
            $school->addRule(Form::FILLED);
        }


        $studyYear = $container->addSelect('study_year', 'Ročník')
                        ->setItems(array(
                            1 => '1. ročník SŠ',
                            2 => '2. ročník SŠ',
                            3 => '3. ročník SŠ',
                            4 => '4. ročník SŠ',
                            6 => '6. ročník ZŠ',
                            7 => '7. ročník ZŠ',
                            8 => '8. ročník ZŠ',
                            9 => '9. ročník ZŠ',
                        ))->setOption('description', 'Kvůli zařazení do kategorie.');

        if ($options & self::REQUIRE_STUDY_YEAR) {
            $studyYear->addRule(Form::FILLED);
        }


        $container->addText('class', 'Třída')
                ->setOption('description', 'Kvůli případné školní korespondenci.');
        
        return $container;
    }

}
