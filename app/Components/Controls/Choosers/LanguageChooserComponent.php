<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Choosers;

use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

final class LanguageChooserComponent extends ChooserComponent
{

    private array $supportedLanguages = [];
    public static array $languageNames = ['cs' => 'Čeština', 'en' => 'English', 'sk' => 'Slovenčina'];

    private string $lang;
    private bool $modifiable;


    public function __construct(Container $container, ?string $lang, bool $modifiable)
    {
        parent::__construct($container);
        $this->lang = $lang;
        $this->modifiable = $modifiable;
    }

    protected function getItem(): NavItem
    {
        if ($this->modifiable) {
            if (!count($this->supportedLanguages)) {
                $this->supportedLanguages = $this->translator->getSupportedLanguages();
            }
            $items = [];
            foreach ($this->supportedLanguages as $language) {
                $items[] = new NavItem(
                    new Title(self::$languageNames[$language]),
                    'this',
                    ['lang' => $language],
                    [],
                    $language === $this->lang
                );
            }

            return new NavItem(
                new Title(self::$languageNames[$this->lang] ?? _('Language'), 'fa fa-language'),
                '#',
                [],
                $items
            );
        }
        return new NavItem(new Title(self::$languageNames[$this->lang]));
    }
}
