<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Spec\Fyziklani;

use FKSDB\Models\Events\Model\ExpressionEvaluator;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Services\PersonHistoryService;
use Nette\Forms\Form;

class SchoolsInTeam extends SchoolCheck
{

    /** @var callable|int */
    private $schoolsInTeam;
    private int $schoolsInTeamValue;
    private ExpressionEvaluator $evaluator;

    /**
     * @param callable|int $schoolsInTeam
     */
    public function __construct(
        $schoolsInTeam,
        ExpressionEvaluator $evaluator,
        PersonHistoryService $personHistoryService
    ) {
        parent::__construct($personHistoryService);
        $this->evaluator = $evaluator;
        $this->setSchoolsInTeam($schoolsInTeam);
    }

    public function getSchoolsInTeam(): int
    {
        if (!isset($this->schoolsInTeamValue)) {
            $this->schoolsInTeamValue = $this->evaluator->evaluate(
                $this->schoolsInTeam,
                $this->holder
            );
        }
        return $this->schoolsInTeamValue;
    }

    /**
     * @param callable|int $schoolsInTeam
     */
    public function setSchoolsInTeam($schoolsInTeam): void
    {
        $this->schoolsInTeam = $schoolsInTeam;
    }

    protected function innerAdjust(Form $form, \FKSDB\Models\Transitions\Holder\ModelHolder $holder): void
    {
        $this->holder = $holder;
        $schoolControls = $this->getControl('p*.person_id.person_history.school_id');
        $personControls = $this->getControl('p*.person_id');

        $msgMixture = sprintf(
            _('Only %d different schools can be represented in the team.'),
            $this->getSchoolsInTeam()
        );
        foreach ($schoolControls as $control) {
            $control->addRule(function () use ($schoolControls, $personControls, $form, $msgMixture): bool {
                $schools = $this->getSchools($schoolControls, $personControls);
                if (!$this->checkMixture($schools)) {
                    $form->addError($msgMixture);
                    return false;
                }
                return true;
            }, $msgMixture);
        }
        $form->onValidate[] = function (Form $form) use ($schoolControls, $personControls, $msgMixture): void {
            //if ($form->isValid()) { // it means that all schools may have been disabled
            $schools = $this->getSchools($schoolControls, $personControls);
            if (!$this->checkMixture($schools)) {
                $form->addError($msgMixture);
            }
            // }
        };
    }

    private function checkMixture(array $schools): bool
    {
        return count(array_unique($schools)) <= $this->getSchoolsInTeam();
    }
}
