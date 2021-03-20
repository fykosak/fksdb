<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContest;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelOrg;
use Fykosak\NetteORM\AbstractService;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelOrg createNewModel(array $data)
 * @method ModelOrg refresh(AbstractModel $model)
 */
class ServiceOrg extends AbstractService {

    public function findByTeXSignature(string $signature, ModelContest $contest): ?ModelOrg {
        if (!$signature) {
            return null;
        }
        $result = $contest->related(DbNames::TAB_ORG)->where('tex_signature', $signature)->fetch();
        return $result ? ModelOrg::createFromActiveRow($result) : null;
    }
}
