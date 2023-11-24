<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\SchoolProvider;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Utils\Html;

/**
 * @phpstan-extends AutocompleteSelectBox<SchoolProvider>
 */
class SchoolSelectField extends AutocompleteSelectBox
{
    /**
     * @throws InvalidLinkException
     */
    public function __construct(Container $container, LinkGenerator $linkGenerator)
    {
        parent::__construct(true, _('School'), 'school');
        $this->setDataProvider(new SchoolProvider($container));
        $link = $linkGenerator->link('Core:School:create');
        $this->setOption(
            'description',
            Html::el()->addText(_('If you cannot find the school, you can add it on '))->addHtml(
                Html::el('a')
                    ->setAttribute('href', $link)
                    ->setAttribute('target', '_blank')
                    ->setText($link)
            )
        );
    }
}
