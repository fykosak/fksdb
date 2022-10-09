<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Modules\Core\Language;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

final class LanguageChooserComponent extends ChooserComponent
{
    private Language $lang;
    private bool $modifiable;

    public function __construct(Container $container, ?Language $lang, bool $modifiable)
    {
        parent::__construct($container);
        $this->lang = $lang;
        $this->modifiable = $modifiable;
    }

    protected function getItem(): NavItem
    {
        if ($this->modifiable) {
            $items = [];
            foreach ($this->translator->getSupportedLanguages() as $language) {
                $supportedLang = Language::tryFrom($language);
                $items[] = new NavItem(
                    new Title(null, $supportedLang->label()),
                    'this',
                    ['lang' => $language],
                    [],
                    $language === $this->lang
                );
            }

            return new NavItem(
                new Title(null, $this->lang->label() ?? _('Language'), 'fa fa-language'),
                '#',
                [],
                $items
            );
        }
        return new NavItem(new Title(null, $this->lang->label()));
    }
}
