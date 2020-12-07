<?php

namespace FKSDB\Model\ORM\Services;


use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\DeprecatedLazyDBTrait;
use FKSDB\Model\ORM\Models\ModelPostContact;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServicePostContact extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_POST_CONTACT, ModelPostContact::class);
    }
}
