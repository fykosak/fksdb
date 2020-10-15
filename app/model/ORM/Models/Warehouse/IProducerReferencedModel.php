<?php

namespace FKSDB\ORM\Models\Warehouse;
/**
 * Interface IProducerReferencedModel
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IProducerReferencedModel {
    public function getProducer(): ?ModelProducer;
}
