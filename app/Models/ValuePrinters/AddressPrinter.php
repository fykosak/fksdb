<?php

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AddressModel;
use Nette\Utils\Html;

class AddressPrinter extends ValuePrinter
{
    /**
     * @param AddressModel $value
     * @throws BadTypeException
     */
    protected function getHtml($value): Html
    {
        if (!$value instanceof AddressModel) {
            throw new BadTypeException(AddressModel::class, $value);
        }
        $container = Html::el('div');
        if (isset($value->first_row)) {
            $container->addHtml(Html::el('div')->addText($value->first_row));
        }
        if (isset($value->second_row)) {
            $container->addHtml(Html::el('div')->addText($value->second_row));
        }
        $container->addHtml(Html::el('div')->addText($value->target));
        $container->addHtml(Html::el('div')->addText($value->postal_code . ' ' . $value->city));
        if ($value->region->country_iso != 'CZ') {
            $container->addHtml(Html::el('div')->addHtml(Html::el('strong')->addText($value->region->name)));
        }
        return $container;
    }
}
