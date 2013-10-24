<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use Nette\Forms\ControlGroup;
use Nette\Forms\Form;
use ServiceContest;
use ServiceSchool;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ContestantFactory {

    const REQUIRE_SCHOOL = 0x1;
    const REQUIRE_STUDY_YEAR = 0x2;
    const SHOW_CONTEST = 0x4;

    /**
     * @var ServiceSchool
     */
    private $serviceSchool;

    /**
     * @var SchoolFactory
     */
    private $factorySchool;

    /**
     * @var ServiceContest
     */
    private $serviceContest;

    function __construct(ServiceSchool $serviceSchool, SchoolFactory $factorySchool, ServiceContest $serviceContest) {
        $this->serviceSchool = $serviceSchool;
        $this->factorySchool = $factorySchool;
        $this->serviceContest = $serviceContest;
    }

    public function createContestant($options = 0, ControlGroup $group = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        if ($options & self::SHOW_CONTEST) {
            $container->addSelect('contest_id', 'Seminář')
                    ->setItems($this->serviceContest->getTable()->fetchPairs('contest_id', 'name'))
                    ->setPrompt('Zvolit seminář')
                    ->addRule(Form::FILLED, 'Je třeba zvoli seminář.');
        }

        $school = $this->factorySchool->createSchoolSelect();
        $container->addComponent($school, 'school_id');

        if ($options & self::REQUIRE_SCHOOL) {
            $school->addRule(Form::FILLED, 'Je třeba zadat školu.');
        }

        // TODO extract this element and made it more robust (show graduation year)
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
                ))->setOption('description', 'Kvůli zařazení do kategorie.')
                ->setPrompt('Zvolit ročník');

        if ($options & self::REQUIRE_STUDY_YEAR) {
            $studyYear->addRule(Form::FILLED, 'Je třeba zadat ročník.');
        }


        $container->addText('class', 'Třída')
                ->setOption('description', 'Kvůli případné školní korespondenci.');

        return $container;
    }

}
