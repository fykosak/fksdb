<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\PhoneRowTrait;
use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\Services\ServiceRegion;
use Nette\Localization\ITranslator;

/**
 * Class PhoneParentDField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class PhoneParentDRow extends AbstractRow {
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
        return _('Telefonní číslo (otec)');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_RESTRICT;
    }
}
