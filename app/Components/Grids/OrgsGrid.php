<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\FilterGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestModel;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\Forms\Form;

class OrgsGrid extends FilterGrid
{
    private ContestModel $contest;

    public function __construct(Container $container, ContestModel $contest)
    {
        parent::__construct($container);
        $this->contest = $contest;
    }

    protected function getModels(): Selection
    {
        $query = $this->contest->getOrganisers()->order('since DESC');
        if (!isset($this->filterParams) || !isset($this->filterParams['term'])) {
            return $query;
        }
        $tokens = preg_split('/\s+/', $this->filterParams['term']);
        foreach ($tokens as $token) {
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

        $this->addColumns([
            'person.full_name',
            'org.since',
            'org.until',
            'org.role',
        ]);

        $this->addORMLink('org.edit', true);
        $this->addORMLink('org.detail', true);
    }
}
