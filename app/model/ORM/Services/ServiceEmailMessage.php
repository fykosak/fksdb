<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEmailMessage;

/**
 * Class ServiceEmailMessage
 * @package FKSDB\ORM\Services
 */
class ServiceEmailMessage extends AbstractServiceSingle {

    /**
     * @inheritDoc
     */
    public function getModelClassName(): string {
        return ModelEmailMessage::class;
    }

    /**
     * @inheritDoc
     */
    protected function getTableName(): string {
        return DbNames::TAB_EMAIL_MESSAGE;
    }
}
