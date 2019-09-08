<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\PhoneRowTrait;
use FKSDB\Components\Forms\Factories\ITestedRowFactory;
use FKSDB\ORM\Services\ServiceRegion;
use Nette\Localization\ITranslator;

/**
 * Class PhoneField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class PhoneRow extends AbstractRow implements ITestedRowFactory {
    use PhoneRowTrait;

    /**
     * PhoneRow constructor.
     * @param ITranslator $translator
     * @param PhoneNumberFactory $phoneNumberFactory
     */
    public function __construct(ITranslator $translator,PhoneNumberFactory $phoneNumberFactory) {
        parent::__construct($translator);
        $this->phoneNumberFactory=$phoneNumberFactory;
    }

    /**
     * @return string
     */
    public function getModelAccessKey(): string {
        return 'phone';
    }


    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Phone number');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_RESTRICT;
    }
}
