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

    /** @var mixed */
    private $schoolsInTeam;

    private int $schoolsInTeamValue;

    private ExpressionEvaluator $evaluator;

    /**
     * SchoolsInTeam constructor.
     * @param mixed $schoolsInTeam
     * @param ExpressionEvaluator $evaluator
     * @param ServicePersonHistory $servicePersonHistory
     */
    public function __construct($schoolsInTeam, ExpressionEvaluator $evaluator, ServicePersonHistory $servicePersonHistory) {
        parent::__construct($servicePersonHistory);
        $this->evaluator = $evaluator;
        $this->setSchoolsInTeam($schoolsInTeam);
    }

    public function getSchoolsInTeam(): int {
        if (!isset($this->schoolsInTeamValue)) {
            $this->schoolsInTeamValue = $this->evaluator->evaluate($this->schoolsInTeam, $this->getHolder());
        }
        return $this->schoolsInTeamValue;
    }

    /**
     * @param mixed $schoolsInTeam
     * @return void
     */
    public function setSchoolsInTeam($schoolsInTeam): void {
        $this->schoolsInTeam = $schoolsInTeam;
    }

    protected function innerAdjust(Form $form, Machine $machine, Holder $holder): void {
        $this->setHolder($holder);
        $schoolControls = $this->getControl('p*.person_id.person_history.school_id');
        $personControls = $this->getControl('p*.person_id');

        $msgMixture = sprintf(_('Only %d different schools can be represented in the team.'), $this->getSchoolsInTeam());
        foreach ($schoolControls as $control) {
            $control->addRule(function (IControl $control) use ($schoolControls, $personControls, $form, $msgMixture): bool {
                $schools = $this->getSchools($schoolControls, $personControls);
                if (!$this->checkMixture($schools)) {
                    $form->addError($msgMixture);
                    return false;
                }
                return true;
            }, $msgMixture);
        }
        $form->onValidate[] = function (Form $form) use ($schoolControls, $personControls, $msgMixture) : void {
            if ($form->isValid()) { // it means that all schools may have been disabled
                $schools = $this->getSchools($schoolControls, $personControls);
                if (!$this->checkMixture($schools)) {
                    $form->addError($msgMixture);
                }
            }
        };
    }

    private function checkMixture(array $schools): bool {
        return count(array_unique($schools)) <= $this->getSchoolsInTeam();
    }
}
