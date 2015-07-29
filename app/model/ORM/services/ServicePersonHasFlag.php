<?php

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServicePersonHasFlag extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_PERSON_HAS_FLAG;
    protected $modelClassName = 'ModelPersonHasFlag';

}