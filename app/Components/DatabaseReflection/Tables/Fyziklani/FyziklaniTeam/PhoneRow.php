<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\PhoneRowTrait;
use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\Services\ServiceRegion;
use Nette\Localization\ITranslator;

/**
 * Class PhoneRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class PhoneRow extends AbstractRow {
    use PhoneRowTrait;

    /**
     * PhoneRow constructor.
     * @param ServiceRegion $serviceRegion
     * @param ITranslator $translator
     */
    public function __construct(ServiceRegion $serviceRegion, ITranslator $translator) {
        parent::__construct($translator);
        $this->registerPhoneRowTrait($serviceRegion);
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Phone');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
