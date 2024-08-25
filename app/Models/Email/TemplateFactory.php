<?php

declare(strict_types=1);

namespace FKSDB\Models\Email;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\Application\Application;
use Nette\Application\IPresenter;
use Nette\Application\IPresenterFactory;
use Nette\Application\UI\TemplateFactory as LatteFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Http\IRequest;

/**
 * @phpstan-type TRenderedData = array{text:string,subject:string}
 */
final class TemplateFactory
{
    private GettextTranslator $translator;
    private IRequest $request;
    private LatteFactory $latteTemplateFactory;
    private ?IPresenter $presenter;

    public function __construct(
        LatteFactory $latteTemplateFactory,
        GettextTranslator $translator,
        IRequest $request,
        Application $application,
        IPresenterFactory $presenterFactory
    ) {
        $this->translator = $translator;
        $this->request = $request;
        $this->latteTemplateFactory = $latteTemplateFactory;
        $this->presenter = $application->getPresenter() ?? $presenterFactory->createPresenter('Organizer:Email');
    }

    /**
     * @throws BadTypeException
     */
    public function create(Language $lang): Template
    {
        if (!$this->presenter instanceof BasePresenter) {
            throw new BadTypeException(BasePresenter::class, $this->presenter);
        }
        $template = $this->latteTemplateFactory->createTemplate();
        if (!$template instanceof Template) {
            throw new BadTypeException(Template::class, $template);
        }
        $template->getLatte()->addProvider('uiControl', $this->presenter);
        $template->getLatte()->addProvider('uiPresenter', $this->presenter);
        $template->control = $this->presenter;
        $template->baseUrl = $this->request->getUrl()->getBaseUrl();
        $template->setTranslator($this->translator, $lang->value);
        return $template;
    }
}
