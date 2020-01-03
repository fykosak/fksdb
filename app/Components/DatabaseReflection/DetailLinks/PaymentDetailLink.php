<?php

namespace FKSDB\Components\DatabaseReflection\Links;

use FKSDB\ORM\Models\ModelOrg;
use FKSDB\ORM\Models\ModelPayment;
use Nette\Application\UI\InvalidLinkException;

/**
 * Class OrgLink
 * @package FKSDB\Components\DatabaseReflection\Links
 */
class PaymentDetailLink extends AbstractLink {
    /**
     * @param ModelPayment $model
     * @return string|void
     * @throws InvalidLinkException
     */
    protected function createLink($model): string {
        return $this->presenterComponent->getPresenter()->link(':Event:Payment:detail', [
            'eventId' => $model->event_id,
            'id' => $model->payment_id,
        ]);
    }

    /**
     * @return string
     */
    protected function getText(): string {
        return _('Detail');
    }
}
