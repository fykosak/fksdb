<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Contestant;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Tests\Adapter;
use Fykosak\NetteORM\Model\Model;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;

/**
 * @phpstan-extends Adapter<ContestantModel,PersonModel>
 */
final class ContestantToPersonAdapter extends Adapter
{
    protected function getModels(Model $model): iterable
    {
        return [$model->person];
    }

    /**
     * @throws InvalidLinkException
     */
    protected function getLogPrepend(Model $model): Html
    {
        return Html::el()
            ->addText(_('In person '))
            ->addHtml(
                Html::el('a')
                    ->addAttributes(
                        ['href' => $this->linkGenerator->link('Organizer:Person:detail', ['id' => $model->person_id])]
                    )
                    ->addText($model->getFullName())
            );
    }

    public function getId(): string
    {
        return 'contestantToPerson';
    }
}
