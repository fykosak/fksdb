<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\PersonInfoModel;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\Service;

/**
 * @method PersonInfoModel|null findByPrimary($key)
 */
final class PersonInfoService extends Service
{
    /**
     * @param PersonInfoModel|null $model
     */
    public function storeModel(array $data, ?Model $model = null): PersonInfoModel
    {
        if (isset($data['agreed'])) {
            if ($data['agreed'] == '1') {
                $data['agreed'] = new \DateTime();
            } elseif ($data['agreed'] == '0') {
                unset($data['agreed']);
            }
        }
        return parent::storeModel($data, $model);
    }
}
