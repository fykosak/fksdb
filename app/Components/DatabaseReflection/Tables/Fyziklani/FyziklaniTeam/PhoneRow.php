<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use FKSDB\Components\DatabaseReflection\PhoneRowTrait;
use FKSDB\Components\Forms\Factories\ITestedRowFactory;
use Nette\Localization\ITranslator;

/**
 * Class PhoneRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class PhoneRow extends AbstractFyziklaniTeamRow implements ITestedRowFactory {
    use PhoneRowTrait;

    /**
     * PhoneRow constructor.
     * @param ITranslator $translator
     * @param PhoneNumberFactory $phoneNumberFactory
     */
    public function __construct(ITranslator $translator, PhoneNumberFactory $phoneNumberFactory) {
        parent::__construct($translator);
        $this->phoneNumberFactory = $phoneNumberFactory;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Phone');
    }

    /**
     * @return string
     */
    public function getModelAccessKey(): string {
        return 'phone';
    }
}
