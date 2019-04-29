<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

/**
 * Class TShirtSizeRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class TShirtSizeRow extends AbstractParticipantRow {
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
}
