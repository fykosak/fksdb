<?php

namespace Submits;

use fks_pdf_parser_exception;
use FPDI;
use ModelSubmit;
use Nette\InvalidStateException;
use Nette\Utils\Strings;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PDFStamper implements IStorageProcessing {

    /**
     * @var string
     */
    private $inputFile;

    /**
     * @var string
     */
    private $outputFile;

    /**
     * @var int used font size in pt
     */
    private $fontSize;

    /**
     *
     * @var string printf mask for arguments: series, label, contestant's name
     */
    private $stampMask;

    function __construct($fontSize, $stampMask = 'S%dU%s, %s') {
        $this->fontSize = $fontSize;
        $this->stampMask = $stampMask;
    }

    public function getInputFile() {
        return $this->inputFile;
    }

    public function setInputFile($inputFile) {
        $this->inputFile = $inputFile;
    }

    public function getOutputFile() {
        return $this->outputFile;
    }

    public function setOutputFile($outputFile) {
        $this->outputFile = $outputFile;
    }

    public function getFontSize() {
        return $this->fontSize;
    }

    public function getStampMask() {
        return $this->stampMask;
    }

    public function process(ModelSubmit $submit) {
        if (!$this->getInputFile()) {
            throw new InvalidStateException('Input file not set.');
        }

        if (!$this->getOutputFile()) {
            throw new InvalidStateException('Output file not set.');
        }

        $series = $submit->getTask()->series;
        $label = $submit->getTask()->label;
        $person = $submit->getContestant()->getPerson();

        $stampText = sprintf($this->getStampMask(), $series, $label, $person->getFullname());
        try {
            $this->stampText($stampText);
        } catch (fks_pdf_parser_exception $e) {
            throw new ProcessingException('Cannot add stamp to the PDF.', null, $e);
        }
    }

    private function stampText($text) {
        $pdf = new FPDI();
        $pagecount = $pdf->setSourceFile($this->getInputFile());

        for ($page = 1; $page <= $pagecount; ++$page) {
            $tpl = $pdf->importPage($page);

            $specs = $pdf->getTemplateSize($tpl);
            $orientation = $specs['h'] > $specs['w'] ? 'P' : 'L';
            $pdf->addPage($orientation);
            $pdf->useTemplate($tpl, 1, 1, 0, 0, true);

            // calculate size of the stamp
            $pdf->SetFont('Courier', 'b', $this->getFontSize());
            $pdf->SetTextColor(0, 0, 0);
            $pw = 210; // pagewidth, A4 210 mm
            $offset = 7; // vertical offset
            $tw = $pdf->GetStringWidth($text);
            $th = $this->getFontSize() * 0.35; // 1pt = 0.35mm
            $x = ($pw - $tw) / 2;
            $y = $th + $offset;

            // stamp background
            $margin = 2;
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Rect($x - $margin, $y - $th - $margin, $tw + 2 * $margin, ($th + 2 * $margin), 'F');

            $stampText = Strings::webalize($text, ' ,.', false); // FPDF has only ASCII encoded fonts
            $pdf->Text($x, $y, $stampText);
        }

        $pdf->Output($this->getOutputFile(), 'F');
    }

}
