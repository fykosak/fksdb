<?php

namespace FKSDB\Models\ORM\Services;

use Fykosak\NetteORM\AbstractService;
use Fykosak\NetteORM\AbstractModel;

/**
 * Service class to high-level manipulation with ORM objects.
 * Use singleton descendant implementations.
 *
 * @note Because of compatibility with PHP 5.2 (no LSB), part of the code has to be
 *       duplicated in all descendant classes.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @author Michal Červeňak <miso@fykos.cz>
 * @deprecated
 * @use AbstractServiceSingle
 * @method AbstractModel storeModel(array $data, ?AbstractModel $model = null)
 */
abstract class OldAbstractServiceSingle extends AbstractService {

    /**
     * @param array $data
     * @return AbstractModel
     * @deprecated
     * @internal Used also in MultiTableSelection.
     */
    public function createFromArray(array $data): AbstractModel {
        $className = $this->getModelClassName();
        $data = $this->filterData($data);
        return new $className($data, $this->getTable());
    }
}
