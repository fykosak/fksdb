<?php

namespace Events\Spec\Fyziklani;

use Events\FormAdjustments\AbstractAdjustment;
use Events\FormAdjustments\IFormAdjustment;
use Events\Model\Holder\Holder;
use Nette\Forms\Controls\BaseControl;
use ServicePersonHistory;

/**
 * More user friendly Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class SchoolCheck extends AbstractAdjustment implements IFormAdjustment {

    /**
     * @var ServicePersonHistory
     */
    private $servicePersonHistory;

    /**
     * @var Holder
     */
    private $holder;

    function __construct(ServicePersonHistory $servicePersonHistory) {
        $this->servicePersonHistory = $servicePersonHistory;
    }

    public function getHolder() {
        return $this->holder;
    }

    public function setHolder(Holder $holder) {
        $this->holder = $holder;
    }

    protected final function getSchools($schoolControls, $personControls) {
        $personIds = array_filter(array_map(function (BaseControl $control) {
            return $control->getValue(false);
        }, $personControls));

        $schools = $this->servicePersonHistory->getTable()
            ->where('person_id', $personIds)
            ->where('ac_year', $this->getHolder()->getEvent()->getAcYear())
            ->fetchPairs('person_id', 'school_id');

        $result = array();
        foreach ($schoolControls as $key => $control) {
            if ($control->getValue()) {
                $result[] = $control->getValue();
            } else if ($personId = $personControls[$key]->getValue(false)) { // intentionally =
                if ($personId && isset($schools[$personId])) {
                    $result[] = $schools[$personId];
                }
            }
        }
        return $result;
    }

}

