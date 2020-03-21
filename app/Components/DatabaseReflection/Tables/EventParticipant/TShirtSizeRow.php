<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class TShirtSizeRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class TShirtSizeRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;
    const SIZE_MAP = [
        'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'
    ];
    const GENDER_MAP = [
        'M' => 'male',
        'F' => 'female',
    ];

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('T-shirt size');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'tshirt_size';
    }
}
