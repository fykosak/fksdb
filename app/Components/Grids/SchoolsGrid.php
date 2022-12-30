<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\FilterBaseGrid;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\SchoolService;
use Fykosak\Utils\UI\Title;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\Utils\Html;

class SchoolsGrid extends FilterBaseGrid
{
    private SchoolService $service;

    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    public function injectService(SchoolService $service): void
    {
        $this->service = $service;
    }

    protected function getModels(): Selection
    {
        $query = $this->service->getTable();
        $tokens = preg_split('/\s+/', $this->searchTerm['term']);
        foreach ($tokens as $token) {
            $query->where('name_full LIKE CONCAT(\'%\', ? , \'%\')', $token);
        }
        return $query;
    }

    /**
     * @throws BadTypeException
     */
    protected function configure(): void
    {
        $this->getColumnsContainer()->addComponent(
            new RendererItem($this->container, fn(SchoolModel $model) => $model->name, new Title(null, _('Name'))),
            'name'
        );
        $this->getColumnsContainer()->addComponent(
            new RendererItem(
                $this->container,
                fn(SchoolModel $school): string => $school->address->city,
                new Title(null, _('City'))
            ),
            'city'
        );
        $this->getColumnsContainer()->addComponent(
            new RendererItem(
                $this->container,
                fn(SchoolModel $row): Html => Html::el('span')
                    ->addAttributes(['class' => ('badge ' . ($row->active ? 'bg-success' : 'bg-danger'))])
                    ->addText(($row->active)),
                new Title(null, _('Active?'))
            ),
            'active'
        );

        $this->addORMLink('school.edit');
        $this->addORMLink('school.detail');
    }
}
