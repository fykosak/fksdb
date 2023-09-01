<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\GettextTranslator;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\Title;

/**
 * @property GettextTranslator $translator
 */
final class LanguageChooserComponent extends ChooserComponent
{
    /**
     * @throws NotImplementedException
     */
    protected function getItem(): NavItem
    {
        $items = [];
        foreach (Language::cases() as $language) {
            $items[] = new NavItem(
                new Title(null, $language->label()),
                'this',
                ['lang' => $language->value],
                [],
                $language->value === $this->translator->lang
            );
        }

        return new NavItem(
            new Title(null, Language::tryFrom($this->translator->lang)->label(), 'fas fa-language'),
            '#',
            [],
            $items
        );
    }
}
