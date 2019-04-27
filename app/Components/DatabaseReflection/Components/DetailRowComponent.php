<?php

namespace FKSDB\Components\DatabaseReflection;

use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class StalkingRowComponent
 * @package FKSDB\Components\Controls\Stalking
 * @property FileTemplate $template
 */
class DetailRowComponent extends RowComponent {
    /**
     * DetailRowComponent constructor.
     * @param ITranslator $translator
     * @param AbstractRow $factory
     * @param string $fieldName
     * @param int $userPermission
     */
    public function __construct(ITranslator $translator, AbstractRow $factory, string $fieldName, int $userPermission) {
        parent::__construct($translator, $factory, $fieldName, $userPermission);
        $this->includeTest = true;
    }
}
