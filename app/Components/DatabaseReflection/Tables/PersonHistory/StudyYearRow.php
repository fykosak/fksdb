<?php

namespace FKSDB\Components\DatabaseReflection\PersonHistory;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use FKSDB\YearCalculator;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Localization\ITranslator;

/**
 * Class StudyYearRow
 * @package FKSDB\Components\DatabaseReflection\PersonHistory
 */
class StudyYearRow extends AbstractRow {
    use DefaultPrinterTrait;
    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * StudyYearRow constructor.
     * @param ITranslator $translator
     * @param YearCalculator $yearCalculator
     */
    public function __construct(ITranslator $translator, YearCalculator $yearCalculator) {
        parent::__construct($translator);
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Study year');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }

    /**
     * @return null|string
     */
    public function getDescription() {
        return _('Kvůli zařazení do kategorie.');
    }

    /**
     * @param int|null $acYear
     * @return BaseControl
     * @throws BadRequestException
     */
    public function createField(int $acYear = null): BaseControl {
        if (\is_null($acYear)) {
            throw new BadRequestException();
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

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'study_year';
    }
}
