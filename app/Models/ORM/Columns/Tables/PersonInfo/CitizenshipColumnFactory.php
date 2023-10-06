<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonInfo;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\CountryModel;
use FKSDB\Models\ORM\Models\PersonInfoModel;
use FKSDB\Models\ORM\Services\CountryService;
use FKSDB\Models\UI\StringPrinter;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<PersonInfoModel,never>
 */
class CitizenshipColumnFactory extends ColumnFactory
{
    private CountryService $countryService;

    public function injectService(CountryService $countryService): void
    {
        $this->countryService = $countryService;
    }

    protected function createFormControl(...$args): BaseControl
    {
        $control = new SelectBox($this->getTitle());
        $control->setItems($this->getCountries());
        $control->setPrompt(_('Choose citizenship'));
        return $control;
    }

    /**
     * @phpstan-return array<string,string>
     */
    private function getCountries(): array
    {
        $results = [];
        /** @var CountryModel $country */
        foreach ($this->countryService->getTable() as $country) {
            $results[$country->alpha_2] = $country->name;
        }
        return $results;
    }

    /**
     * @param PersonInfoModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return StringPrinter::getHtml($model->citizenship);
    }
}
