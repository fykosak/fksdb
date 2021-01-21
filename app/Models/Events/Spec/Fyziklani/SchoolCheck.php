<?php

namespace FKSDB\Models\Events\Spec\Fyziklani;

use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\Events\FormAdjustments\AbstractAdjustment;
use FKSDB\Models\Events\FormAdjustments\IFormAdjustment;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\ORM\Services\ServicePersonHistory;
use FKSDB\Models\Persons\ModelDataConflictException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\IControl;

/**
 * More user friendly Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class SchoolCheck extends AbstractAdjustment implements IFormAdjustment {

    private ServicePersonHistory $servicePersonHistory;
    private Holder $holder;

    public function __construct(ServicePersonHistory $servicePersonHistory) {
        $this->servicePersonHistory = $servicePersonHistory;
    }

    public function getHolder(): Holder {
        return $this->holder;
    }

    public function setHolder(Holder $holder): void {
        $this->holder = $holder;
    }

    /**
     * @param IControl[] $schoolControls
     * @param IControl[]|ReferencedId[] $personControls
     * @return array
     */
    final protected function getSchools(array $schoolControls, array $personControls): array {
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
            $personId = $personControls[$key]->getValue(false);
            if ($control->getValue()) {
                $result[] = $control->getValue();
            } elseif ($personId) { // intentionally =
                if (isset($schools[$personId])) {
                    $result[] = $schools[$personId];
                }
            }
        }
        return $result;
    }
}
