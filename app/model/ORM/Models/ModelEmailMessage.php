<?php

namespace FKSDB\ORM\Models;

use DateTime;
use FKSDB\ORM\AbstractModelSingle;

/**
 * Class ModelEmailMessage
 * @package FKSDB\ORM\Models
 * @property-read int email_message_id`
 * @property-read string recipient
 * @property-read string sender
 * @property-read string reply_to
 * @property-read string subject
 * @property-read string|null carbon_copy
 * @property-read string|null blind_carbon_copy
 * @property-read string text
 * @property-read string state
 * @property-read DateTime created
 * @property-read DateTime sent
 */
class ModelEmailMessage extends AbstractModelSingle {

}
