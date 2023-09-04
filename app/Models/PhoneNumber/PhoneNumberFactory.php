<?php

declare(strict_types=1);

namespace FKSDB\Models\PhoneNumber;

use FKSDB\Components\Badges\FlagBadge;
use FKSDB\Models\ORM\Models\CountryModel;
use FKSDB\Models\ORM\Services\CountryService;
use Fykosak\NetteORM\TypedSelection;
use Nette\Utils\Html;

class PhoneNumberFactory
{
    private CountryService $countryService;
    /** @phpstan-var TypedSelection<CountryModel> */
    private TypedSelection $table;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
        $this->table = $this->countryService->getTable();
    }

    public function formatPhone(string $number): Html
    {
        try {
            $country = $this->getCountry($number);
            if ($country) {
                return Html::el('span')->addHtml(FlagBadge::getHtml($country))
                    ->addText($country->formatPhoneNumber($number));
            }
        } catch (InvalidPhoneNumberException $exception) {
        }
        return Html::el('span')->addAttributes(['class' => 'badge bg-danger'])->addText($number);
    }

    private function getCountry(string $number): ?CountryModel
    {
        /** @var CountryModel $country */
        foreach ($this->table as $country) {
            if ($country->matchPhone($number)) {
                return $country;
            }
        }
        return null;
    }

    public function isValid(string $number): bool
    {
        return (bool)$this->getCountry($number);
    }
}
