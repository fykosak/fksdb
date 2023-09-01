<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\Forms\Control;
use Nette\Forms\Form;

/**
 * @phpstan-extends AbstractAdjustment<BaseHolder>
 */
class UniqueCheck extends AbstractAdjustment
{
    private string $field;
    private string $message;
    private EventParticipantService $eventParticipantService;

    public function __construct(string $field, string $message, EventParticipantService $eventParticipantService)
    {
        $this->field = $field;
        $this->message = $message;
        $this->eventParticipantService = $eventParticipantService;
    }

    /**
     * @param BaseHolder $holder
     */
    protected function innerAdjust(Form $form, ModelHolder $holder): void
    {
        $control = $this->getControl($this->field);
        if (!$control) {
            return;
        }
        /** @phpstan-ignore-next-line */
        $control->addRule(function (Control $control) use ($holder): bool {
            $query = $this->eventParticipantService->getTable();
            $column = BaseHolder::getBareColumn($this->field);
            if ($control instanceof ReferencedId) {
                /* We don't want to fulfill potential promise
                 * as it would be out of transaction here.
                 */
                $value = $control->getValue(false);
            } else {
                $value = $control->getValue();
            }

            $query->where($column, $value);
            $query->where('event_participant.event_id', $holder->event->event_id);
            if ($holder->getModel()) {
                $query->where("NOT event_participant_id = ?", $holder->getModel()->getPrimary());
            }
            return $query->count('*') === 0;
        }, $this->message);
    }
}
