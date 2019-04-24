<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelOrg;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceOrg extends AbstractServiceSingle {

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelOrg::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_ORG;
    }

    /**
     * Syntactic sugar.
     *
     * @param mixed $signature
     * @param mixed $contest_id
     * @return ModelOrg|null
     */
    public function findByTeXSignature($signature, $contest_id) {
        if (!$signature) {
            return null;
        }
        $result = $this->getTable()->where('tex_signature', $signature)
            ->where('contest_id', $contest_id)->fetch();
        return $result ? ModelOrg::createFromActiveRow($result) : null;
    }

}

