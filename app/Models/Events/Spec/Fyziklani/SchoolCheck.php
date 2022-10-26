<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Spec\Fyziklani;

use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\Events\FormAdjustments\AbstractAdjustment;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Services\PersonHistoryService;
use FKSDB\Models\Persons\ModelDataConflictException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Control;

abstract class SchoolCheck extends AbstractAdjustment
{

    private PersonHistoryService $personHistoryService;
    protected BaseHolder $holder;

    public function __construct(PersonHistoryService $personHistoryService)
    {
        $this->personHistoryService = $personHistoryService;
    }

    /**
     * @param Control[] $schoolControls
     * @param Control[]|ReferencedId[] $personControls
     */
    final protected function getSchools(array $schoolControls, array $personControls): array
    {
        $personIds = array_filter(
            array_map(function (BaseControl $control) {
                try {
                    if ($control instanceof ReferencedId) {
                        /* We don't want to fulfill potential promise
                         * as it would be out of transaction here.
                         */
                        return $control->getValue(false);
                    } else {
                        return $control->getValue();
                    }
                } catch (ModelDataConflictException $exception) {
                    $control->addError(
                        sprintf(
                            _('Some fields of the group "%s" do not match an existing record.'),
                            $control->getLabel()
                        )
                    );
                }
            }, $personControls)
        );

        $schools = $this->personHistoryService->getTable()
            ->where('person_id', $personIds)
            ->where('ac_year', $this->holder->event->getContestYear()->ac_year)
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
