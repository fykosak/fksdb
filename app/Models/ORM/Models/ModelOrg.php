<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Nette\Database\Table\ActiveRow;
use Nette\Security\Resource;
use Fykosak\NetteORM\AbstractModel;

/**
 * @property-read ActiveRow contest
 * @property-read ActiveRow person
 * @property-read int since
 * @property-read int contest_id
 * @property-read int|null until
 * @property-read int org_id
 * @property-read int person_id
 * @property-read string role
 * @property-read int order
 * @property-read string contribution
 * @property-read string tex_signature
 * @property-read string domain_alias
 */
class ModelOrg extends AbstractModel implements Resource
{

    public const RESOURCE_ID = 'org';

    public function getContest(): ModelContest
    {
        return ModelContest::createFromActiveRow($this->contest);
    }

    public function getPerson(): ModelPerson
    {
        return ModelPerson::createFromActiveRow($this->person);
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
