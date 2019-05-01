<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\Tables\Traits\PersonNameTrait;

/**
 * Class PersonNameRowFactory
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class PersonNameRowFactory extends AbstractParticipantRow {
    use PersonNameTrait;
}
