<?php

namespace FKSDB\Components\DatabaseReflection\PersonHistory;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\YearCalculator;
use http\Exception\InvalidArgumentException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * Class StudyYearRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class StudyYearRow extends AbstractColumnFactory {

    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * StudyYearRow constructor.
     * @param YearCalculator $yearCalculator
     */
    public function __construct(YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
    }

    public function getTitle(): string {
        return _('Study year');
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_BASIC, self::PERMISSION_ALLOW_BASIC);
    }

    /**
     * @return string|null
     */
    public function getDescription() {
        return _('Kvůli zařazení do kategorie.');
    }

    /**
     * @param array $args
     * @return BaseControl
     * @throws InvalidArgumentException
     */
    public function createField(...$args): BaseControl {
        list($acYear) = $args;
        if (\is_null($acYear)) {
            throw new \InvalidArgumentException();
        }
        $control = new SelectBox($this->getTitle());
        $control->setItems($this->createOptions($acYear));
        $control->setOption('description', $this->getDescription());
        $control->setPrompt(_('Zvolit ročník'));
        return $control;
    }

    /**
     * @param int $acYear
     * @return array
     */
    private function createOptions(int $acYear) {
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

    protected function getModelAccessKey(): string {
        return 'study_year';
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }
}
