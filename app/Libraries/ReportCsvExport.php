<?php

namespace App\Libraries;

use CodeIgniter\HTTP\ResponseInterface;

class ReportCsvExport
{
    /**
     * @param list<string>        $headers
     * @param list<list<mixed>>  $rows
     */
    public static function downloadResponse(
        ResponseInterface $response,
        string $filename,
        array $headers,
        array $rows
    ): ResponseInterface {
        $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename) ?: 'report.csv';
        if (! str_ends_with(strtolower($safeName), '.csv')) {
            $safeName .= '.csv';
        }

        $out = fopen('php://temp', 'r+');
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $safeName . '"')
            ->setBody("\xEF\xBB\xBF" . $csv);
    }
}
