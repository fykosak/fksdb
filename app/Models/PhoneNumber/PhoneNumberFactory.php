<?php

declare(strict_types=1);

namespace FKSDB\Models\PhoneNumber;

use FKSDB\Models\ORM\Models\RegionModel;
use FKSDB\Models\ORM\Services\RegionService;
use Fykosak\NetteORM\TypedSelection;
use Nette\Utils\Html;

class PhoneNumberFactory
{
    private RegionService $regionService;

    private TypedSelection $table;

    public function __construct(RegionService $regionService)
    {
        $this->regionService = $regionService;
        $this->table = $this->regionService->getTable();
    }

    private function getAllRegions(): TypedSelection
    {
        return $this->table;
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
                                'class' => 'flag-icon flag-icon-' . \strtolower($region->country_iso),
                            ])
                    );
                return Html::el('span')->addHtml($flag)->addText($region->formatPhoneNumber($number));
            }
        } catch (InvalidPhoneNumberException $exception) {
        }
        return Html::el('span')->addAttributes(['class' => 'badge bg-danger'])->addText($number);
    }

    private function getRegion(string $number): ?RegionModel
    {
        /** @var RegionModel $region */
        foreach ($this->getAllRegions() as $region) {
            if ($region->matchPhone($number)) {
                return $region;
            }
        }
        return null;
    }

    public function isValid(string $number): bool
    {
        return !!$this->getRegion($number);
    }
}
