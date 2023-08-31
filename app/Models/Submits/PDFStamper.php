<?php

declare(strict_types=1);
namespace FKSDB\Models\Submits;

use FKSDB\Models\ORM\Models\SubmitModel;
use Nette\InvalidStateException;
use Nette\Utils\Strings;
use Tracy\Debugger;

//Debugger::$logDirectory = '/var/www/html/fksdb/log';

class PDFStamper implements StorageProcessing
{

    private string $inputFile;

    private string $outputFile;

    /** @var int used font size in pt, currently set at app/config/config.neon */
    private int $fontSize;
    public function __construct(int $fontSize)
    {
        $this->fontSize = $fontSize;
    }

    public function getInputFile(): string
    {
        return $this->inputFile;
    }

    public function setInputFile(string $filename): void
    {
        $this->inputFile = $filename;
    }

    public function getOutputFile(): string
    {
        return $this->outputFile;
    }

    public function setOutputFile(string $filename): void
    {
        $this->outputFile = $filename;
    }

    public function getFontSize(): int
    {
        return $this->fontSize;
    }

    /**
     * @throws ProcessingException
     */
    public function process(SubmitModel $submit): void
    {
        if (!$this->getInputFile()) {
            throw new InvalidStateException(_('Input file not set.'));
        }

        if (!$this->getOutputFile()) {
            throw new InvalidStateException(_('Output file not set.'));
        }

        $series = $submit->task->series;
        $label = $submit->task->label;
        $person = $submit->contestant->person;

        $stampText = sprintf('S%dU%s, %s, %s', $series, $label, $person->getFullName(), $submit->submit_id);
        try {
            $this->stampText($stampText);
        } catch (\Throwable $exception) {
            throw new ProcessingException(_('Cannot add stamp to the PDF.'), 0, $exception);
        }
    }

    private function stampText(string $text): void
    {
        $pdf = new \setasign\Fpdi\Fpdi();
        $pageCount = $pdf->setSourceFile($this->getInputFile());
        
        for ($page = 1; $page <= $pageCount; ++$page) {
            $tpl = $pdf->importPage($page);
            $actText = $text . ' page ' . $page . '/' . $pageCount;
            $specs = $pdf->getTemplateSize($tpl);
            
            $orientation = $specs['orientation'];
            Debugger::log(gettype($orientation)); //gets string
            $pdf->AddPage($orientation);
            $pdf->useTemplate($tpl, 1, 1, null, null, true);

            // calculate size of the stamp
            $pdf->SetFont('Courier', 'b', $this->getFontSize());
            $pdf->SetDrawColor(0, 0, 0);// @phpstan-ignore-line
            $pw = 210; // pagewidth, A4 210 mm
            $offset = 7; // vertical offset
            $tw = $pdf->GetStringWidth($actText); // @phpstan-ignore-line
            $th = $this->getFontSize() * 0.35; // 1pt = 0.35mm
            $x = ($pw - $tw) / 2;
            $y = $th + $offset;
            // stamp background
            $margin = 2;
            $pdf->SetFillColor(240, 240, 240); // @phpstan-ignore-line
            $pdf->Rect( // @phpstan-ignore-line
                $x - $margin,
                $y - $th - $margin,
                $tw + 2 * $margin,
                ($th + 2 * $margin),
                'F'
            );

            $stampText = Strings::webalize($actText, ' ,.', false); // FPDF has only ASCII encoded fonts
            $pdf->Text($x, $y, $stampText);// @phpstan-ignore-line
        }

        $pdf->Output($this->getOutputFile(), 'F');// @phpstan-ignore-line
    }
}
