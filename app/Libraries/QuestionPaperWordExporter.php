<?php

namespace App\Libraries;

/**
 * Exports question paper as editable MS Word (.docx or .doc).
 */
class QuestionPaperWordExporter
{
    /**
     * @param array<string, mixed> $config
     * @param list<array<string, mixed>> $typeSections
     * @return array{
     *   body: ?string,
     *   path: ?string,
     *   filename: string,
     *   mime: string,
     *   format: string
     * }
     */
    public function export(
        array $config,
        array $typeSections,
        bool $showAnswers,
        bool $includeAnswerKey,
        bool $filenameAsKey = false
    ): array {
        if (class_exists(\PhpOffice\PhpWord\PhpWord::class) && class_exists(\ZipArchive::class)) {
            try {
                $path = (new QuestionPaperDocxExporter())->buildTempFile(
                    $config,
                    $typeSections,
                    $showAnswers,
                    $includeAnswerKey
                );

                if ($this->isValidDocxFile($path)) {
                    return [
                        'body'     => null,
                        'path'     => $path,
                        'filename' => $this->buildFilename($config, 'docx', $filenameAsKey),
                        'mime'     => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'format'   => 'docx',
                    ];
                }

                if (is_file($path)) {
                    @unlink($path);
                }
            } catch (\Throwable $e) {
                log_message('error', 'Question paper DOCX export failed: ' . $e->getMessage());
            }
        }

        $html = view('admin/question_paper/word_document', [
            'config'           => $config,
            'typeSections'     => $typeSections,
            'showAnswers'      => $showAnswers,
            'includeAnswerKey' => $includeAnswerKey,
        ]);

        return [
            'body'     => $html,
            'path'     => null,
            'filename' => $this->buildFilename($config, 'doc', $filenameAsKey),
            'mime'     => 'application/msword',
            'format'   => 'doc',
        ];
    }

    /**
     * @param array<string, mixed> $config
     */
    public function buildFilename(array $config, string $ext, bool $keyOnly): string
    {
        $title = trim((string) ($config['header']['title'] ?? ''));
        if ($title === '') {
            $title = 'question_paper';
        }
        $title = preg_replace('/[^a-zA-Z0-9\x{0600}-\x{06FF}_-]+/u', '_', $title) ?? 'question_paper';
        $title = trim($title, '_');
        if ($title === '') {
            $title = 'question_paper';
        }
        if ($keyOnly) {
            $title .= '_answer_key';
        }

        return $title . '.' . $ext;
    }

    protected function isValidDocxFile(string $path): bool
    {
        if (!is_file($path) || filesize($path) < 800) {
            return false;
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return false;
        }

        $sig = fread($handle, 4);
        fclose($handle);

        if ($sig !== "PK\x03\x04") {
            return false;
        }

        if (!class_exists(\ZipArchive::class)) {
            return true;
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return false;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!is_string($xml) || $xml === '') {
            return false;
        }

        $prev = libxml_use_internal_errors(true);
        $doc  = simplexml_load_string($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        return $doc !== false;
    }
}
