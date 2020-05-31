<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelOrg;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceOrg extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelOrg::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_ORG;
    }

    public function findByTeXSignature(string $signature, int $contestId): ?ModelOrg {
        if (!$signature) {
            return null;
        }
        /** @var ModelOrg|false $result */
        $result = $this->getTable()->where('tex_signature', $signature)
            ->where('contest_id', $contestId)->fetch();
        return $result ?: null;
    }
}
