<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class TShirtSizeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TShirtSizeRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    public const SIZE_MAP = [
        'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'
    ];
    public const GENDER_MAP = [
        'M' => 'male',
        'F' => 'female',
    ];

    public function getTitle(): string {
        return _('T-shirt size');
    }

    protected function getModelAccessKey(): string {
        return 'tshirt_size';
    }
}
