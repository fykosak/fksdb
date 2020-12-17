<?php

namespace FKSDB\Model\ORM\Models;

/**
 * Interface IPersonReferencedModel
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IPersonReferencedModel {
    public function getPerson(): ?ModelPerson;
}
