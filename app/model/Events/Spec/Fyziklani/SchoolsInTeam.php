<?php

namespace FKSDB\Events\Spec\Fyziklani;

use FKSDB\Events\FormAdjustments\IFormAdjustment;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\ExpressionEvaluator;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\ORM\Services\ServicePersonHistory;
use Nette\Forms\Form;
use Nette\Forms\IControl;

/**
 * More user friendly Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SchoolsInTeam extends SchoolCheck implements IFormAdjustment {

    /**
     * @var mixed
     */
    private $schoolsInTeam;

    /**
     * @var int
     */
    private $schoolsInTeamValue;

    /**
     * @var ExpressionEvaluator
     */
    private $evaluator;

    /**
     * @return int|mixed
     */
    public function getSchoolsInTeam() {
        if ($this->schoolsInTeamValue === null) {
            $this->schoolsInTeamValue = $this->evaluator->evaluate($this->schoolsInTeam, $this->getHolder());
        }
        return $this->schoolsInTeamValue;
    }

    /**
     * @param $schoolsInTeam
     * @return void
     */
    public function setSchoolsInTeam($schoolsInTeam) {
        $this->schoolsInTeam = $schoolsInTeam;
    }

    /**
     * SchoolsInTeam constructor.
     * @param $schoolsInTeam
     * @param ExpressionEvaluator $evaluator
     * @param ServicePersonHistory $servicePersonHistory
     */
    public function __construct($schoolsInTeam, ExpressionEvaluator $evaluator, ServicePersonHistory $servicePersonHistory) {
        parent::__construct($servicePersonHistory);
        $this->evaluator = $evaluator;
        $this->setSchoolsInTeam($schoolsInTeam);
    }

    /**
     * @param Form $form
     * @param Machine $machine
     * @param Holder $holder
     * @return void
     */
    protected function _adjust(Form $form, Machine $machine, Holder $holder) {
        $this->setHolder($holder);
        $schoolControls = $this->getControl('p*.person_id.person_history.school_id');
        $personControls = $this->getControl('p*.person_id');

        $msgMixture = sprintf(_('V týmu můžou být soutežící nejvýše z %d škol.'), $this->getSchoolsInTeam());
        foreach ($schoolControls as $control) {
            $control->addRule(function (IControl $control) use ($schoolControls, $personControls, $form, $msgMixture) {
                $schools = $this->getSchools($schoolControls, $personControls);
                if (!$this->checkMixture($schools)) {
                    $form->addError($msgMixture);
                    return false;
                }
                return true;
            }, $msgMixture);
        }
        $form->onValidate[] = function (Form $form) use ($schoolControls, $personControls, $msgMixture) {
            if ($form->isValid()) { // it means that all schools may have been disabled
                $schools = $this->getSchools($schoolControls, $personControls);
                if (!$this->checkMixture($schools)) {
                    $form->addError($msgMixture);
                }
            }
        };
    }

    /**
     * @param $schools
     * @return bool
     */
    private function checkMixture($schools) {
        return count(array_unique($schools)) <= $this->getSchoolsInTeam();
    }

}
