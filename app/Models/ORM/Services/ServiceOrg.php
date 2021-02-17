<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelOrg;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelOrg createNewModel(array $data)
 * @method ModelOrg refresh(AbstractModelSingle $model)
 */
class ServiceOrg extends AbstractServiceSingle {



    public function findByTeXSignature(string $signature, int $contestId): ?ModelOrg {
        if (!$signature) {
            return null;
        }
        /** @var ModelOrg|null $result */
        $result = $this->getTable()->where('tex_signature', $signature)
            ->where('contest_id', $contestId)->fetch();
        return $result ?: null;
    }

    public function store(?ModelOrg $model, array $data): ModelOrg {
        if (is_null($model)) {
            return $this->createNewModel($data);
        } else {
            $this->updateModel2($model, $data);
            return $this->refresh($model);
        }
    }
}
