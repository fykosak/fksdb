<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

final class LanguageChooserComponent extends ChooserComponent
{

    public static array $languageNames = ['cs' => 'Čeština', 'en' => 'English', 'sk' => 'Slovenčina'];

    private Language $lang;
    private bool $modifiable;


    public function __construct(Container $container, string $lang, bool $modifiable)
    {
        parent::__construct($container);
        $this->lang = Language::tryFrom($lang);
        $this->modifiable = $modifiable;
    }

    /**
     * @throws NotImplementedException
     */
    protected function getItem(): NavItem
    {
        if ($this->modifiable) {
            $items = [];
            foreach (Language::cases() as $language) {
                $items[] = new NavItem(
                    new Title(null, $language->label()),
                    'this',
                    ['lang' => $language->value],
                    [],
                    $language->value === $this->lang
                );
            }

            return new NavItem(
                new Title(null, $this->lang->label(), 'fas fa-language'),
                '#',
                [],
                $items
            );
        }
        return new NavItem(new Title(null, $this->lang->label()));
    }
}
