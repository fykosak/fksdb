<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelPersonInfo extends AbstractModelSingle {

    public static function createFromTableRow(NTableRow $row) {
        return new self($row->toArray(), $row->getTable());
    }

    /**
     * @return ModelPerson
     */
    public function getPerson() {
        return ModelPerson::createFromTableRow($this->ref(DbNames::TAB_PERSON, 'person_id'));
    }

}

