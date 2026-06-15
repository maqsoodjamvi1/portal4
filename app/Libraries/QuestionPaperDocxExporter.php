<?php

namespace App\Libraries;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\LineSpacingRule;

/**
 * Builds an editable .docx question paper (requires phpoffice/phpword).
 */
class QuestionPaperDocxExporter
{
    protected QuestionPaperService $paperService;

    public function __construct(?QuestionPaperService $paperService = null)
    {
        $this->paperService = $paperService ?? new QuestionPaperService();
    }

    /**
     * @param array<string, mixed> $config
     * @param list<array<string, mixed>> $typeSections
     */
    public function buildTempFile(array $config, array $typeSections, bool $showAnswers, bool $includeAnswerKey): string
    {
        Settings::setOutputEscapingEnabled(true);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'marginLeft'   => 720,
            'marginRight'  => 720,
            'marginTop'    => 720,
            'marginBottom' => 720,
        ]);

        $this->writeHeader($section, $config);
        $this->writeTypeSections($section, $config, $typeSections, $showAnswers);

        if ($includeAnswerKey && ($config['layout']['paper_mode'] ?? '') === 'both') {
            $section->addPageBreak();
            $this->addCenteredText($section, 'Answer Key', ['bold' => true, 'size' => 14]);
            $this->writeTypeSections($section, $config, $typeSections, true);
        }

        $dir = WRITEPATH . 'cache' . DIRECTORY_SEPARATOR . 'question_paper';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = $dir . DIRECTORY_SEPARATOR . 'qp_' . uniqid('', true) . '.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($path);

        return $path;
    }

    /**
     * @param array<string, mixed> $config
     * @param list<array<string, mixed>> $typeSections
     */
    protected function writeTypeSections($section, array $config, array $typeSections, bool $showAnswers): void
    {
        $layout     = $config['layout'] ?? [];
        $descChoice = $layout['descriptive_choice'] ?? [];

        foreach ($typeSections as $si => $block) {
            if (!empty($layout['page_break_topic']) && $si > 0) {
                $section->addPageBreak();
            }

            $this->addBlankLine($section);

            $sectionTitle = (string) ($block['title'] ?? 'Section');
            $secMarks     = (float) ($block['section_marks'] ?? 0);
            if ($secMarks > 0) {
                $sectionTitle .= ' (' . QuestionPaperService::formatMarksValue($secMarks) . ' marks)';
            }

            $this->addCenteredText($section, $sectionTitle, ['bold' => true, 'size' => 13]);

            $sectionQuestions = $block['questions'] ?? [];
            $isDescriptive    = ($block['type_key'] ?? '') === 'descriptive';
            $choiceNote       = $isDescriptive
                ? $this->paperService->descriptiveChoiceSectionNote($descChoice, count($sectionQuestions))
                : '';

            if ($choiceNote !== '') {
                $this->addCenteredText($section, $choiceNote, ['italic' => true, 'size' => 11]);
            }

            $qMarkLabel = !empty($layout['show_question_marks'])
                ? (string) ($block['marks_per_question_label'] ?? '')
                : '';

            $usePairs = $isDescriptive
                && ($descChoice['mode'] ?? '') === 'pairs'
                && !empty($descChoice['pairs']);

            if ($usePairs) {
                $items = $this->paperService->buildDescriptiveDisplayItems($sectionQuestions, $descChoice);
                foreach ($items as $item) {
                    if (($item['type'] ?? '') === 'or') {
                        $this->addCenteredText($section, 'OR', ['bold' => true, 'size' => 12]);
                        continue;
                    }
                    $showNum = !isset($item['show_number']) || !empty($item['show_number']);
                    $romanLabel = $showNum
                        ? QuestionPaperService::toRoman((int) ($item['roman'] ?? 1))
                        : '';
                    $this->writeQuestion(
                        $section,
                        $item['q'] ?? [],
                        $romanLabel,
                        $qMarkLabel,
                        $layout,
                        $showAnswers,
                        $showNum
                    );
                    if (!empty($item['pair_end'])) {
                        $this->addBlankLine($section);
                    }
                }
            } else {
                $roman = 0;
                foreach ($sectionQuestions as $q) {
                    $roman++;
                    $this->writeQuestion(
                        $section,
                        $q,
                        QuestionPaperService::toRoman($roman),
                        $qMarkLabel,
                        $layout,
                        $showAnswers
                    );
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function writeHeader($section, array $config): void
    {
        $h = $config['header'] ?? [];

        $logoPath = $this->resolveLocalPath((string) ($h['school_logo_url'] ?? ''));
        if ($logoPath !== null) {
            try {
                $section->addImage($logoPath, [
                    'width'            => 55,
                    'height'           => 55,
                    'alignment'        => Jc::START,
                    'wrappingStyle'    => 'inline',
                ]);
            } catch (\Throwable $e) {
                // skip broken logo
            }
        }

        if (!empty($h['school_name'])) {
            $this->addCenteredText($section, (string) $h['school_name'], ['bold' => true, 'size' => 16]);
        }
        if (!empty($h['school_campus'])) {
            $this->addCenteredText($section, (string) $h['school_campus'], ['size' => 11]);
        }

        if (!empty($h['title'])) {
            $this->addCenteredText($section, (string) $h['title'], ['bold' => true, 'size' => 14]);
        }

        if (!empty($h['subject']) || !empty($h['class_label']) || !empty($h['total_marks'])) {
            $metaParts = [];
            if (!empty($h['subject'])) {
                $metaParts[] = 'Subject: ' . $h['subject'];
            }
            if (!empty($h['class_label'])) {
                $metaParts[] = 'Class: ' . $h['class_label'];
            }
            if (!empty($h['total_marks'])) {
                $metaParts[] = 'Marks: ' . $h['total_marks'];
            }
            $this->addCenteredText($section, implode("\t\t\t", $metaParts), ['bold' => true, 'size' => 11]);
        }

        $secondary = [];
        if (!empty($h['exam_date'])) {
            $secondary[] = 'Date: ' . $h['exam_date'];
        }
        if (!empty($h['exam_time'])) {
            $secondary[] = 'Time: ' . $h['exam_time'];
        }
        if (!empty($h['duration'])) {
            $secondary[] = 'Duration: ' . $h['duration'];
        }
        if ($secondary !== []) {
            $this->addCenteredText($section, implode('    ', $secondary), ['size' => 10]);
        }

        if (!empty($h['show_name']) || !empty($h['show_roll']) || !empty($h['show_section'])) {
            $fields = [];
            if (!empty($h['show_name'])) {
                $fields[] = 'Name: _________________________';
            }
            if (!empty($h['show_roll'])) {
                $fields[] = 'Roll No: __________';
            }
            if (!empty($h['show_section'])) {
                $fields[] = 'Section: __________';
            }
            $this->addText($section, implode('    ', $fields));
        }

        if (!empty($h['instructions'])) {
            $this->addText($section, 'Instructions: ' . (string) $h['instructions'], ['bold' => true]);
        }
    }

    /**
     * @param array<string, mixed> $q
     * @param array<string, mixed> $layout
     */
    protected function writeQuestion(
        $section,
        array $q,
        string $roman,
        string $qMarkLabel,
        array $layout,
        bool $showAnswers,
        bool $showNumber = true
    ): void {
        $type = strtolower((string) ($q['question_type'] ?? 'mcq'));
        $text = trim((string) ($q['question'] ?? ''));

        $prefix = '';
        if ($showNumber && $roman !== '') {
            $prefix = $roman . '. ';
        } elseif (!$showNumber) {
            $prefix = '    ';
        }

        $line = $prefix;
        if ($text !== '') {
            $line .= $text;
        }
        if ($qMarkLabel !== '') {
            $line .= ' (' . $qMarkLabel . ' marks)';
        }

        $questionFont = ($type === 'mcq' || $type === 'mcq_multi') ? ['bold' => true] : null;
        $this->addText($section, $line, $questionFont);

        if ($type === 'mcq' || $type === 'mcq_multi') {
            $this->writeMcqOptionsLine($section, $q, $type, $showAnswers);
        } elseif ($type === 'tf') {
            if ($showAnswers) {
                $tf = strtolower(trim((string) ($q['answer_text'] ?? '')));
                $this->addText($section, '    Answer: ' . ($tf === 'true' ? 'True' : 'False'), ['bold' => true]);
            } else {
                $this->addText($section, '    (   ) True     (   ) False');
            }
        } elseif ($type === 'fill') {
            if ($showAnswers) {
                $this->addText($section, '    Answer: ' . (string) ($q['answer_text'] ?? ''));
            } else {
                $this->addText($section, '    Answer: _________________________________');
            }
        } elseif ($type === 'short') {
            if ($showAnswers) {
                $this->addText($section, '    Answer: ' . (string) ($q['answer_text'] ?? ''));
            } else {
                $this->addText($section, '    _________________________________');
                $this->addText($section, '    _________________________________');
            }
        } elseif ($type === 'descriptive') {
            $descAnswerSpace = !empty($layout['descriptive_answer_space']);
            $descLines       = $descAnswerSpace
                ? max(1, min(12, (int) ($layout['descriptive_lines'] ?? 6)))
                : 0;
            if ($showAnswers) {
                $this->addText($section, '    Model answer:', ['italic' => true]);
                $this->addText($section, '    ' . (string) ($q['answer_text'] ?? ''));
            } elseif ($descLines > 0) {
                for ($i = 0; $i < $descLines; $i++) {
                    $this->addText($section, '    ________________________________________________');
                }
            }
        } elseif ($type === 'match') {
            $pairs = $q['match_pairs'] ?? [];
            if (is_array($pairs) && $pairs !== []) {
                $rights = array_values($pairs);
                if (!$showAnswers) {
                    shuffle($rights);
                }
                $this->addText($section, '    Column A', ['bold' => true]);
                foreach ($pairs as $pi => $p) {
                    $this->addText($section, '    ' . ($pi + 1) . '. ' . (string) ($p['left'] ?? ''));
                }
                $this->addText($section, '    Column B', ['bold' => true]);
                foreach ($rights as $pi => $p) {
                    $this->addText($section, '    ' . chr(65 + $pi) . '. ' . (string) ($p['right'] ?? ''));
                }
                if ($showAnswers) {
                    foreach ($pairs as $pi => $p) {
                        $this->addText($section, '    ' . ($pi + 1) . '. ' . ($p['left'] ?? '') . ' → ' . ($p['right'] ?? ''));
                    }
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $q
     */
    protected function writeMcqOptionsLine($section, array $q, string $type, bool $showAnswers): void
    {
        $correct = strtoupper(trim((string) ($q['correct_option'] ?? 'A')));
        $correctSet = [];
        if ($type === 'mcq_multi' && is_array($q['correct_options'] ?? null)) {
            foreach ($q['correct_options'] as $co) {
                $correctSet[strtoupper((string) $co)] = true;
            }
        }

        $parts = [];
        foreach (['A', 'B', 'C', 'D'] as $opt) {
            $field   = 'option_' . strtolower($opt);
            $optText = trim((string) ($q[$field] ?? ''));
            if ($optText === '') {
                continue;
            }
            $isCor   = $type === 'mcq' ? ($correct === $opt) : isset($correctSet[$opt]);
            $parts[] = [
                'text'  => $opt . '. ' . $optText,
                'bold'  => $showAnswers && $isCor,
            ];
        }

        if ($parts === []) {
            return;
        }

        $needsMixedStyle = $showAnswers && array_filter(array_column($parts, 'bold'));
        if (!$needsMixedStyle) {
            $labels = array_map(static fn (array $p): string => $p['text'], $parts);
            $this->addText($section, '    ' . implode('     ', $labels));

            return;
        }

        $run = $section->addTextRun($this->paragraphStyle());
        $run->addText('    ');
        foreach ($parts as $i => $part) {
            if ($i > 0) {
                $run->addText('     ');
            }
            $font = $part['bold'] ? ['bold' => true] : null;
            $run->addText($part['text'], $font);
        }
    }

    /**
     * Single line spacing, no extra space before/after paragraphs.
     *
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    protected function addBlankLine($section): void
    {
        $section->addTextBreak(1);
    }

    protected function paragraphStyle(array $extra = []): array
    {
        return array_merge([
            'spaceAfter'      => 0,
            'spaceBefore'     => 0,
            'spacing'         => 0,
            'spacingLineRule' => LineSpacingRule::AUTO,
        ], $extra);
    }

    protected function addCenteredText($section, string $text, array $font = []): void
    {
        $this->addText($section, $text, $font, ['alignment' => Jc::CENTER]);
    }

    /**
     * @param array<string, mixed> $font
     * @param array<string, mixed> $para
     */
    protected function addText($section, string $text, ?array $font = null, array $para = []): void
    {
        $text = $this->sanitizeDocxText($text);
        if (trim($text) === '') {
            return;
        }

        if ($this->isUrdu($text)) {
            $para = array_merge(['alignment' => Jc::END, 'bidiVisual' => true], $para);
        }

        $para = array_merge($this->paragraphStyle(), $para);
        $font = ($font !== null && $font !== []) ? $font : null;

        $section->addText($text, $font, $para);
    }

    protected function sanitizeDocxText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? $text;
    }

    protected function isUrdu(string $text): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $text);
    }

    protected function resolveLocalPath(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        $base = rtrim(base_url(), '/');
        if (str_starts_with($url, $base)) {
            $rel  = ltrim(substr($url, strlen($base)), '/');
            $path = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $rel);
            if (is_file($path)) {
                return $path;
            }
        }

        if (is_file($url)) {
            return $url;
        }

        return null;
    }
}
