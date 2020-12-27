<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Tables\PersonHistory;

use FKSDB\Models\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Models\DBReflection\MetaDataFactory;
use FKSDB\Models\ValuePrinters\StringPrinter;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\YearCalculator;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * Class StudyYearRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class StudyYearColumnFactory extends DefaultColumnFactory {

    private YearCalculator $yearCalculator;

    public function __construct(YearCalculator $yearCalculator, MetaDataFactory $metaDataFactory) {
        parent::__construct($metaDataFactory);
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @param array $args
     * @return BaseControl
     * @throws \InvalidArgumentException
     */
    protected function createFormControl(...$args): BaseControl {
        [$acYear] = $args;
        if (\is_null($acYear)) {
            throw new \InvalidArgumentException();
        }
        $control = new SelectBox($this->getTitle());
        $control->setItems($this->createOptions($acYear));
        $control->setOption('description', $this->getDescription());
        $control->setPrompt(_('Choose study year'));
        return $control;
    }

    private function createOptions(int $acYear): array {
        $hsYears = [];
        foreach (range(1, 4) as $studyYear) {
            $hsYears[$studyYear] = sprintf(_('grade %d (expected graduation in %d)'),
                $studyYear,
                $this->yearCalculator->getGraduationYear($studyYear, $acYear));
        }

        $primaryYears = [];
        foreach (range(6, 9) as $studyYear) {
            $primaryYears[$studyYear] = sprintf(_('grade %d (expected graduation in %d)'),
                $studyYear,
                $this->yearCalculator->getGraduationYear($studyYear, $acYear));
        }

        return [
            _('high school') => $hsYears,
            _('primary school') => $primaryYears,
        ];
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }
}
