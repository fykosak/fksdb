<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\CountryService;
use FKSDB\Models\ORM\Services\SchoolService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-extends BaseGrid<SchoolModel,array{
 *     term?:string,
 * }>
 */
final class SchoolsGrid extends BaseGrid
{
    private SchoolService $service;
    private CountryService $countryService;

    public function injectService(SchoolService $service, CountryService $countryService): void
    {
        $this->service = $service;
        $this->countryService = $countryService;
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('term')->setHtmlAttribute('placeholder', _('Find'));
        $form->addCheckbox('not_verified', _('Not verified'));
        $form->addText('city', _('City'));
        $country = $form->addSelect('country', _('Country'));
        $country->setItems($this->countryService->getTable()->fetchPairs('country_id', 'name'));
    }

    /**
     * @phpstan-return TypedSelection<SchoolModel>
     */
    protected function getModels(): TypedSelection
    {
        $query = $this->service->getTable();
        foreach ($this->filterParams as $key => $value) {
            if (!$value) {
                continue;
            }
            switch ($key) {
                case 'city':
                    $query->where('address.city LIKE CONCAT(\'%\', ?, \'%\')', $value);
                    break;
                case 'country':
                    $query->where('address.country_id', $value);
                    break;
                case 'not_verified':
                    $query->where('verified = FALSE');
                    break;
                case 'term':
                    $tokens = explode(' ', $value);
                    foreach ($tokens as $token) {
                        $query->whereOr([
                            'school.name_full LIKE CONCAT(\'%\', ?, \'%\')' => $token,
                            'school.name LIKE CONCAT(\'%\', ?, \'%\')' => $token,
                            'school.name_abbrev LIKE CONCAT(\'%\', ?, \'%\')' => $token,
                            'address.city LIKE CONCAT(\'%\', ?, \'%\')' => $token,
                            'address.country.name LIKE CONCAT(\'%\', ?, \'%\')' => $token,
                        ]);
                    }
                    break;
            }
        }
        return $query;
    }

    /**
     * @throws BadTypeException
     */
    protected function configure(): void
    {
        $this->filtered = true;
        $this->paginate = true;
        $this->counter = true;
        $this->addSimpleReferencedColumns([
            '@school.name_full',
            '@school.name',
            '@school.name_abbrev',
        ]);
        $this->addTableColumn( //@phpstan-ignore-line
            new TemplateItem( //@phpstan-ignore-line
                $this->container,
                '@address.city (@country.name) @country.flag',
                '@address.city:title'
            ),
            'city'
        );
        $this->addSimpleReferencedColumns([
            '@school.active',
            '@school.verified',
        ]);

        $this->addTableColumn(
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
                                ->addText(_('High'))
                        );
                    }
                    if ($school->study_u) {
                        $has = true;
                        $container->addHtml(
                            Html::el('span')
                                ->addAttributes(['class' => 'badge bg-warning'])
                                ->addText(_('University'))
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

        $this->addLink('school.edit');
        $this->addLink('school.detail');
    }
}
