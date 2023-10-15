<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\OrganizerModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends BaseGrid<OrganizerModel,array{
 *    term?:string,
 * }>
 */
final class OrganizersGrid extends BaseGrid
{
    private ContestModel $contest;

    public function __construct(Container $container, ContestModel $contest)
    {
        parent::__construct($container);
        $this->contest = $contest;
    }

    /**
     * @phpstan-return TypedGroupedSelection<OrganizerModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        $query = $this->contest->getOrganizers()->order('since DESC');
        if (!isset($this->filterParams['term'])) {
            return $query;
        }
        $tokens = preg_split('/\s+/', $this->filterParams['term']);
        foreach ($tokens as $token) { //@phpstan-ignore-line
            $query->where(
                'CONCAT(person.family_name, person.other_name, IFNULL(org.role,\'\'), IFNULL(org.contribution,\'\'))
                            LIKE CONCAT(\'%\', ? , \'%\')',
                $token
            );
        }
        return $query;
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('term')->setHtmlAttribute('placeholder', _('Find'));
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->filtered = true;
        $this->paginate = true;
        $this->counter = true;
        $this->addSimpleReferencedColumns([
            '@person.full_name',
            '@org.since',
            '@org.until',
            '@org.role',
            '@org.allow_wiki',
            '@org.allow_pm',
        ]);

        $this->addORMLink('org.edit', true);
        $this->addORMLink('org.detail', true);
    }
}
