<?php

namespace FKSDB\Models\ORM\Models;

/**
 * Interface IPersonReferencedModel
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IPersonReferencedModel {
    public function getPerson(): ?ModelPerson;
}
