<?php

namespace FKSDB\ORM\Models;

/**
 * Class IPersonReferencedModel
 * @package FKSDB\ORM\Models
 */
interface IPersonReferencedModel {
    /**
     * @return ModelPerson
     */
    public function getPerson();
}
