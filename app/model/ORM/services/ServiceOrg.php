<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceOrg extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_ORG;
    protected $modelClassName = 'ModelOrg';

    /**
     * Syntactic sugar.
     * 
     * @param ModelContest $contest
     * @param string $signature
     * @return ModelOrg|null
     */
    public function findByTeXSignature(ModelContest $contest, $signature) {
        $result = $this->getTable()->where(array(
                    'contest_id' => $contest->contest_id,
                    'tex_signature' => $signature,
                ))->fetch();

        if ($result !== false) {
            return ModelOrg::createFromTableRow($result);
        } else {
            return null;
        }
    }

}

