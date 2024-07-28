<?php

declare(strict_types=1);

namespace FKSDB\Models\Email;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\GettextTranslator;
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
    private IPresenterFactory $presenterFactory;

    public function __construct(
        LatteFactory $latteTemplateFactory,
        IPresenterFactory $presenterFactory,
        GettextTranslator $translator,
        IRequest $request
    ) {
        $this->translator = $translator;
        $this->request = $request;
        $this->latteTemplateFactory = $latteTemplateFactory;
        $this->presenterFactory = $presenterFactory;
    }

    /**
     * @throws BadTypeException
     */
    public function create(Language $lang): Template
    {
        $presenter = $this->presenterFactory->createPresenter('Organizer:Email');
        if (!$presenter instanceof BasePresenter) {
            throw new BadTypeException(BasePresenter::class, $presenter);
        }
        $presenter->setParent($presenter);
        $template = $this->latteTemplateFactory->createTemplate();
        if (!$template instanceof Template) {
            throw new BadTypeException(Template::class, $template);
        }
        $template->getLatte()->addProvider('uiControl', $presenter);
        $template->control = $presenter;
        $template->baseUrl = $this->request->getUrl()->getBaseUrl();
        $template->setTranslator($this->translator, $lang->value);
        return $template;
    }
}
