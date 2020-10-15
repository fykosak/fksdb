<?php

namespace FKSDB\ORM\Models\Warehouse;
/**
 * Interface IProducerReferencedModel
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IProductReferencedModel {
    public function getProduct(): ?ModelProduct;
}
