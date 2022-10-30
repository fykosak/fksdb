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
            $region = $this->getRegion($number);
            if ($region) {
                $flag = Html::el('span')
                    ->addAttributes(['class' => 'phone-flag me-3'])
                    ->addHtml(
                        Html::el('i')
                            ->addAttributes([
                                'class' => 'flag-icon flag-icon-' . \strtolower($region->alpha_2),
                            ])
                    );
                return Html::el('span')->addHtml($flag)->addText($region->formatPhoneNumber($number));
            }
        } catch (InvalidPhoneNumberException $exception) {
        }
        return Html::el('span')->addAttributes(['class' => 'badge bg-danger'])->addText($number);
    }

    private function getRegion(string $number): ?CountryModel
    {
        /** @var CountryModel $region */
        foreach ($this->table as $region) {
            if ($region->matchPhone($number)) {
                return $region;
            }
        }
        return null;
    }

    public function isValid(string $number): bool
    {
        return (bool)$this->getRegion($number);
    }
}
