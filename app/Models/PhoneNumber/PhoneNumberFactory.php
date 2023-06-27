<?php

declare(strict_types=1);

namespace FKSDB\Models\PhoneNumber;

use FKSDB\Models\ORM\Models\CountryModel;
use FKSDB\Models\ORM\Services\CountryService;
use Fykosak\NetteORM\TypedSelection;
use Nette\Utils\Html;

class PhoneNumberFactory
{
    private CountryService $countryService;
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
                $flag = Html::el('span')
                    ->addAttributes(['class' => 'phone-flag me-3'])
                    ->addHtml(
                        Html::el('i')
                            ->addAttributes([
                                'class' => 'flag-icon flag-icon-' . \strtolower($country->alpha_2),
                            ])
                    );
                return Html::el('span')->addHtml($flag)->addText($country->formatPhoneNumber($number));
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
