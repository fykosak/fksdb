<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Badges\FlagBadge;
use FKSDB\Components\Grids\Components\FilterGrid;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\SchoolService;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-extends FilterGrid<SchoolModel,array{
 *     term?:string,
 * }>
 */
class SchoolsGrid extends FilterGrid
{
    private SchoolService $service;

    public function injectService(SchoolService $service): void
    {
        $this->service = $service;
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('term')->setHtmlAttribute('placeholder', _('Find'));
    }

    /**
     * @phpstan-return TypedSelection<SchoolModel>
     */
    protected function getModels(): TypedSelection
    {
        $query = $this->service->getTable();
        if (!isset($this->filterParams['term'])) {
            return $query;
        }
        $tokens = preg_split('/\s+/', $this->filterParams['term']);
        foreach ($tokens as $token) { //@phpstan-ignore-line
            $query->where('name_full LIKE CONCAT(\'%\', ? , \'%\')', $token);
        }
        return $query;
    }

    /**
     * @throws BadTypeException
     */
    protected function configure(): void
    {
        $this->addColumn(
            new RendererItem(
                $this->container,
                fn(SchoolModel $school) => $school->name_full ?? $school->name,
                new Title(null, _('Full name'))
            ),
            'full_name'
        );
        $this->addColumn(
            new RendererItem(
                $this->container,
                fn(SchoolModel $school): Html => Html::el('span')
                    ->addText($school->address->city . ' (' . $school->address->country->name . ')')
                    ->addHtml(FlagBadge::getHtml($school->address->country)),
                new Title(null, _('City'))
            ),
            'city'
        );
        $this->addColumn(
            new RendererItem(
                $this->container,
                fn(SchoolModel $school): Html => Html::el('span')
                    ->addAttributes(['class' => ('badge ' . ($school->active ? 'bg-success' : 'bg-danger'))])
                    ->addText($school->active),
                new Title(null, _('Active'))
            ),
            'active'
        );
        $this->addColumn(
            new RendererItem(
                $this->container,
                function (SchoolModel $school): Html {
                    $container = Html::el('span');
                    $has = false;
                    if ($school->study_p) {
                        $has = true;
                        $container->addHtml(
                            Html::el('span')
                                ->addAttributes(['class' => 'badge bg-primary'])
                                ->addText(_('Primary'))
                        );
                    }
                    if ($school->study_h) {
                        $has = true;
                        $container->addHtml(
                            Html::el('span')
                                ->addAttributes(['class' => 'badge bg-success'])
                                ->addText(_('Hight'))
                        );
                    }
                    if ($school->study_u) {
                        $has = true;
                        $container->addHtml(
                            Html::el('span')
                                ->addAttributes(['class' => 'badge bg-warning'])
                                ->addText(_('High'))
                        );
                    }
                    if (!$has) {
                        $container->addHtml(
                            Html::el('span')
                                ->addAttributes(['class' => 'badge bg-danger'])
                                ->addText(_('No study year'))
                        );
                    }
                    return $container;
                },
                new Title(null, _('Study'))
            ),
            'study'
        );

        $this->addORMLink('school.edit');
        $this->addORMLink('school.detail');
    }
}
