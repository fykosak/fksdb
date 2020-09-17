<?php

namespace FKSDB\DBReflection\ColumnFactories\PersonHistory;

use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\DBReflection\MetaDataFactory;
use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\YearCalculator;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * Class StudyYearRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class StudyYearRow extends DefaultColumnFactory {

    private YearCalculator $yearCalculator;

    /**
     * StudyYearRow constructor.
     * @param YearCalculator $yearCalculator
     * @param MetaDataFactory $metaDataFactory
     */
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
        $control->setPrompt(_('Zvolit ročník'));
        return $control;
    }

    private function createOptions(int $acYear): array {
        $hsYears = [];
        foreach (range(1, 4) as $studyYear) {
            $hsYears[$studyYear] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'),
                $studyYear,
                $this->yearCalculator->getGraduationYear($studyYear, $acYear));
        }

        $primaryYears = [];
        foreach (range(6, 9) as $studyYear) {
            $primaryYears[$studyYear] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'),
                $studyYear,
                $this->yearCalculator->getGraduationYear($studyYear, $acYear));
        }

        return [
            _('střední škola') => $hsYears,
            _('základní škola nebo víceleté gymnázium') => $primaryYears,
        ];
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }
}
