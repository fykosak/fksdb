<?php

use \Nette\Forms\Form;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormContainerContestant extends FormContainerModel {

    public function __construct(ServiceSchool $serviceSchool, $requiredSchoolInfo = true) {
        parent::__construct(null, null);

        $this->addText('class', 'Třída');

        $schools = $serviceSchool->getSchools()->fetchPairs('school_id', 'full_name');
        //TODO komponenta výběru školy
        $school = $this->addSelect('school_id', 'Škola')
                ->setItems($schools);

        $studyYear = $this->addSelect('study_year', 'Ročník')
                ->setItems(array(
            1 => '1. ročník SŠ',
            2 => '2. ročník SŠ',
            3 => '3. ročník SŠ',
            4 => '4. ročník SŠ',
            6 => '6. ročník ZŠ',
            7 => '7. ročník ZŠ',
            8 => '8. ročník ZŠ',
            9 => '9. ročník ZŠ',
                ));
        
        if ($requiredSchoolInfo) {
            $school->addRule(Form::FILLED);
            $studyYear->addRule(Form::FILLED);
        }
    }

}
