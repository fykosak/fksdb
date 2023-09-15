<?php

declare(strict_types=1);

namespace FKSDB\Models\UI;

use FKSDB\Models\ORM\Models\AddressModel;
use Nette\Utils\Html;

class AddressPrinter
{
    public static function getHtml(?AddressModel $value): Html
    {
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
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
        if ($value->country->alpha_2 != 'CZ') {
            $container->addHtml(Html::el('div')->addHtml(Html::el('strong')->addText($value->country->name)));
        }
        return $container;
    }
}
