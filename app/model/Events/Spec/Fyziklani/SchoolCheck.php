<?php

namespace FKSDB\Events\Spec\Fyziklani;

use FKSDB\Events\FormAdjustments\AbstractAdjustment;
use FKSDB\Events\FormAdjustments\IFormAdjustment;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Components\Forms\Controls\ModelDataConflictException;
use FKSDB\ORM\Services\ServicePersonHistory;
use Nette\Forms\Controls\BaseControl;

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

    /**
     * SchoolCheck constructor.
     * @param ServicePersonHistory $servicePersonHistory
     */
    function __construct(ServicePersonHistory $servicePersonHistory) {
        $this->servicePersonHistory = $servicePersonHistory;
    }

    /**
     * @return Holder
     */
    public function getHolder() {
        return $this->holder;
    }

    /**
     * @param Holder $holder
     */
    public function setHolder(Holder $holder) {
        $this->holder = $holder;
    }

    /**
     * @param $schoolControls
     * @param $personControls
     * @return array
     */
    protected final function getSchools($schoolControls, $personControls) {
        $personIds = array_filter(array_map(function (BaseControl $control) {
            try {
                return $control->getValue();
            } catch (ModelDataConflictException $exception) {
                $control->addError(sprintf(_('Některá pole skupiny "%s" neodpovídají existujícímu záznamu.'), $control->getLabel()));
            }
        }, $personControls));

        $schools = $this->servicePersonHistory->getTable()
            ->where('person_id', $personIds)
            ->where('ac_year', $this->getHolder()->getPrimaryHolder()->getEvent()->getAcYear())
            ->fetchPairs('person_id', 'school_id');

        $result = [];
        foreach ($schoolControls as $key => $control) {
            if ($control->getValue()) {
                $result[] = $control->getValue();
            } elseif ($personId = $personControls[$key]->getValue(false)) { // intentionally =
                if ($personId && isset($schools[$personId])) {
                    $result[] = $schools[$personId];
                }
            }
        }
        return $result;
    }
}
