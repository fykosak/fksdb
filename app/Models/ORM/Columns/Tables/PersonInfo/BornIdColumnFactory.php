<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonInfo;

use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnly;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnlyInput;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\PersonGender;
use FKSDB\Models\ORM\Models\PersonInfoModel;
use FKSDB\Models\UI\StringPrinter;
use Fykosak\NetteORM\Model\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\OutOfRangeException;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<PersonInfoModel,never>
 */
class BornIdColumnFactory extends ColumnFactory
{
    protected function createFormControl(...$args): BaseControl
    {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setOption('description', $this->getDescription());
        $control->addCondition(Form::FILLED)
            ->addRule(fn(BaseControl $control) => self::validate($control), _('Invalid bornID format'));
        return $control;
    }

    /**
     * @param PersonInfoModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return StringPrinter::getHtml($model->born_id);
    }

    public static function validate(BaseControl $control): bool
    {
        $rc = $control->getValue();
        // suppose once validated is always valid
        if ($rc == WriteOnly::VALUE_ORIGINAL) {
            return true;
        }

        // "be liberal in what you receive"
        try {
            [$year, $month, $day, $ext, $controlNumber] = self::parseBornNumber($rc);
        } catch (OutOfRangeException $exception) {
            return false;
        }

        // do roku 1954 přidělovaná devítimístná RČ nelze ověřit
        if (is_null($controlNumber)) {
            return $year < 54;
        }

        // kontrolní číslice
        $mod = (int)(sprintf("%02d%02d%02d%03d", $year, $month, $day, $ext)) % 11;
        if ($mod === 10) {
            $mod = 0;
        }
        if ($mod !== $controlNumber) {
            return false;
        }

        $originalYear = $year;
        $originalMonth = $month;
        $originalDay = $day;
        // kontrola data
        $year += $year < 54 ? 2000 : 1900;

        // k měsíci může být připočteno 20, 50 nebo 70
        if ($month > 70 && $year > 2003) {
            $month -= 70;
        } elseif ($month > 50) {
            $month -= 50;
        } elseif ($month > 20 && $year > 2003) {
            $month -= 20;
        }

        if (!checkdate($month, $day, $year)) {
            return false;
        }

        $normalized = sprintf("%02d%02d%02d/%03d%d", $originalYear, $originalMonth, $originalDay, $ext, $controlNumber);
        $control->setValue($normalized);
        // cislo je OK
        return true;
    }

    /**
     * @phpstan-return (int|null)[] [year,month,day,extension,control]
     * @throws OutOfRangeException
     */
    private static function parseBornNumber(string $bornNumber): array
    {
        if (!preg_match('#^\s*(\d\d)(\d\d)(\d\d)[ /]*(\d\d\d)(\d?)\s*$#', $bornNumber, $matches)) {
            throw new OutOfRangeException('Born number not match');
        }

        [, $year, $month, $day, $ext, $control] = $matches;
        return [(int)$year, (int)$month, (int)$day, (int)$ext, ($control === '') ? null : (int)$control];
    }

    /**
     * @throws OutOfRangeException
     */
    public static function getGender(string $bornNumber): PersonGender
    {
        [, $month, , , $control] = self::parseBornNumber($bornNumber);

        // do roku 1954 přidělovaná devítimístná RČ nelze ověřit
        if (is_null($control)) {
            throw new OutOfRangeException('Born number before 1954');
        }
        return +$month > 50 ? PersonGender::from(PersonGender::FEMALE) : PersonGender::from(PersonGender::MALE);
    }

}
