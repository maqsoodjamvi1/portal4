<?php
// cleanup_orphan_files.php
// Run this script from command line: php cleanup_orphan_files.php

namespace App\Controllers\Admin;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class CleanupOrphanFiles
{
    protected $db;
    protected $uploadPath;
    protected $studentPhotosPath;
    protected $staffPhotosPath;
    protected $attachmentsPath;
    protected $documentsPath;
    protected $audioPath;
    protected $videoPath;
    
    public function __construct()
    {
        // Database connection
        $this->db = \Config\Database::connect();
        
        // Define upload paths (adjust based on your actual structure)
        $this->uploadPath = FCPATH . 'uploads/';  // Change to your actual upload directory
        $this->studentPhotosPath = $this->uploadPath . 'student_photos/';
        $this->staffPhotosPath = $this->uploadPath . 'staff_photos/';
        $this->attachmentsPath = $this->uploadPath . 'attachments/';
        $this->documentsPath = $this->uploadPath . 'documents/';
        $this->audioPath = $this->uploadPath . 'audio/';
        $this->videoPath = $this->uploadPath . 'video/';
    }
    
    /**
     * Get all valid file paths from database
     */
    public function getValidFilePaths()
    {
        $validPaths = [];
        
        // 1. Student profile photos
        $studentPhotos = $this->db->table('students')
            ->select('profile_photo')
            ->where('profile_photo IS NOT NULL')
            ->where('profile_photo !=', '')
            ->get()
            ->getResult();
        
        foreach ($studentPhotos as $photo) {
            $validPaths[] = $this->cleanPath($photo->profile_photo);
        }
        
        // 2. Staff profile photos
        $staffPhotos = $this->db->table('users')
            ->select('photo')
            ->where('photo IS NOT NULL')
            ->where('photo !=', '')
            ->get()
            ->getResult();
        
        foreach ($staffPhotos as $photo) {
            $validPaths[] = $this->cleanPath($photo->photo);
        }
        
        // 3. Attachments from attachements table
        $attachments = $this->db->table('attachements')
            ->select('attachement_path')
            ->where('attachement_path IS NOT NULL')
            ->where('attachement_path !=', '')
            ->get()
            ->getResult();
        
        foreach ($attachments as $attachment) {
            $validPaths[] = $this->cleanPath($attachment->attachement_path);
        }
        
        // 4. Test result attachments
        $testAttachments = $this->db->table('test_results')
            ->select('attachment')
            ->where('attachment IS NOT NULL')
            ->where('attachment !=', '')
            ->get()
            ->getResult();
        
        foreach ($testAttachments as $attachment) {
            $validPaths[] = $this->cleanPath($attachment->attachment);
        }
        
        // 5. Class dairy videos
        $dairyVideos = $this->db->table('classdairy')
            ->select('video_url')
            ->where('video_url IS NOT NULL')
            ->where('video_url !=', '')
            ->get()
            ->getResult();
        
        foreach ($dairyVideos as $video) {
            $validPaths[] = $this->cleanPath($video->video_url);
        }
        
        // 6. Class dairy audio
        $dairyAudio = $this->db->table('d_class_dairy_audio')
            ->select('dairy_audio')
            ->where('dairy_audio IS NOT NULL')
            ->where('dairy_audio !=', '')
            ->get()
            ->getResult();
        
        foreach ($dairyAudio as $audio) {
            $validPaths[] = $this->cleanPath($audio->dairy_audio);
        }
        
        // 7. Skill documents
        $skillDocs = $this->db->table('skill_document')
            ->select('document_url, document_screenshot')
            ->get()
            ->getResult();
        
        foreach ($skillDocs as $doc) {
            if (!empty($doc->document_url)) {
                $validPaths[] = $this->cleanPath($doc->document_url);
            }
            if (!empty($doc->document_screenshot)) {
                $validPaths[] = $this->cleanPath($doc->document_screenshot);
            }
        }
        
        // 8. Notices audio
        $noticesAudio = $this->db->table('notices')
            ->select('notice_audio')
            ->where('notice_audio IS NOT NULL')
            ->where('notice_audio !=', '')
            ->get()
            ->getResult();
        
        foreach ($noticesAudio as $notice) {
            $validPaths[] = $this->cleanPath($notice->notice_audio);
        }
        
        // 9. Top level planning audio
        $tlpAudio = $this->db->table('top_level_planning')
            ->select('audio_url')
            ->where('audio_url IS NOT NULL')
            ->where('audio_url !=', '')
            ->get()
            ->getResult();
        
        foreach ($tlpAudio as $audio) {
            $validPaths[] = $this->cleanPath($audio->audio_url);
        }
        
        // 10. Weekly planning documents and audio
        $weeklyDocs = $this->db->table('weekly_planning')
            ->select('doc_url, audio_url')
            ->where('doc_url IS NOT NULL OR audio_url IS NOT NULL')
            ->get()
            ->getResult();
        
        foreach ($weeklyDocs as $doc) {
            if (!empty($doc->doc_url)) {
                $validPaths[] = $this->cleanPath($doc->doc_url);
            }
            if (!empty($doc->audio_url)) {
                $validPaths[] = $this->cleanPath($doc->audio_url);
            }
        }
        
        // 11. PDF documents
        $pdfDocs = $this->db->table('pdf_documents')
            ->select('name')
            ->get()
            ->getResult();
        
        foreach ($pdfDocs as $doc) {
            if (!empty($doc->name)) {
                $validPaths[] = $this->cleanPath($doc->name);
            }
        }
        
        // 12. Quiz questions with images
        $quizImages = $this->db->table('qb_questions')
            ->select('question_image')
            ->where('question_image IS NOT NULL')
            ->where('question_image !=', '')
            ->get()
            ->getResult();
        
        foreach ($quizImages as $image) {
            $validPaths[] = $this->cleanPath($image->question_image);
        }
        
        // 13. Vocabulary bank (if any images)
        $vocabImages = $this->db->table('vocab_bank')
            ->select('question_image')
            ->where('question_image IS NOT NULL')
            ->where('question_image !=', '')
            ->get()
            ->getResult();
        
        foreach ($vocabImages as $image) {
            $validPaths[] = $this->cleanPath($image->question_image);
        }
        
        // Clean and normalize paths
        $validPaths = array_unique($validPaths);
        $validPaths = array_filter($validPaths, function($path) {
            return !empty($path);
        });
        
        return $validPaths;
    }
    
    /**
     * Clean file path to get relative path from upload directory
     */
    private function cleanPath($path)
    {
        // Remove base URL if present
        $baseUrl = base_url();
        $path = str_replace($baseUrl, '', $path);
        
        // Remove common prefixes
        $prefixes = [
            '/uploads/',
            'uploads/',
            'public/uploads/',
            '/public/uploads/',
            'resources/',
            '/resources/',
            'assets/',
            '/assets/',
            'student_photos/',
            'staff_photos/',
            'attachments/',
            'documents/',
            'audio/',
            'video/'
        ];
        
        foreach ($prefixes as $prefix) {
            if (strpos($path, $prefix) !== false) {
                $path = substr($path, strpos($path, $prefix) + strlen($prefix));
                break;
            }
        }
        
        // Remove leading slashes
        $path = ltrim($path, '/');
        
        // If path contains directory structure, keep it
        return $path;
    }
    
    /**
     * Scan upload directory and find orphan files
     */
    public function findOrphanFiles()
    {
        echo "=== SCANNING UPLOAD DIRECTORY ===\n\n";
        
        $validPaths = $this->getValidFilePaths();
        echo "Total valid file references in database: " . count($validPaths) . "\n\n";
        
        $orphanFiles = [];
        $totalSize = 0;
        $fileCount = 0;
        
        if (!is_dir($this->uploadPath)) {
            echo "ERROR: Upload directory not found: " . $this->uploadPath . "\n";
            return $orphanFiles;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->uploadPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $fileCount++;
                $relativePath = str_replace($this->uploadPath, '', $file->getPathname());
                $relativePath = str_replace('\\', '/', $relativePath);
                
                // Check if file is referenced in database
                $isReferenced = false;
                foreach ($validPaths as $validPath) {
                    // Check exact match or if file is part of the path
                    if (strpos($relativePath, $validPath) !== false || 
                        strpos($validPath, basename($relativePath)) !== false ||
                        basename($relativePath) == basename($validPath)) {
                        $isReferenced = true;
                        break;
                    }
                }
                
                if (!$isReferenced) {
                    $fileSize = $file->getSize();
                    $orphanFiles[] = [
                        'path' => $relativePath,
                        'full_path' => $file->getPathname(),
                        'size' => $fileSize,
                        'size_mb' => round($fileSize / 1024 / 1024, 2),
                        'modified' => date('Y-m-d H:i:s', $file->getMTime())
                    ];
                    $totalSize += $fileSize;
                }
            }
        }
        
        echo "Total files scanned: " . $fileCount . "\n";
        echo "Orphan files found: " . count($orphanFiles) . "\n";
        echo "Total orphan size: " . round($totalSize / 1024 / 1024, 2) . " MB\n\n";
        
        return $orphanFiles;
    }
    
    /**
     * Delete orphan files
     */
    public function deleteOrphanFiles($dryRun = true)
    {
        $orphanFiles = $this->findOrphanFiles();
        
        if (empty($orphanFiles)) {
            echo "No orphan files found to delete.\n";
            return;
        }
        
        echo "=== ORPHAN FILES LIST ===\n\n";
        
        $deletedCount = 0;
        $deletedSize = 0;
        $failedCount = 0;
        
        foreach ($orphanFiles as $file) {
            if ($dryRun) {
                echo "[DRY RUN] Would delete: {$file['path']} ({$file['size_mb']} MB)\n";
                $deletedCount++;
                $deletedSize += $file['size'];
            } else {
                if (unlink($file['full_path'])) {
                    echo "[DELETED] {$file['path']} ({$file['size_mb']} MB)\n";
                    $deletedCount++;
                    $deletedSize += $file['size'];
                } else {
                    echo "[FAILED] Could not delete: {$file['path']}\n";
                    $failedCount++;
                }
            }
        }
        
        echo "\n=== SUMMARY ===\n";
        echo "Dry Run Mode: " . ($dryRun ? "YES" : "NO") . "\n";
        echo "Files to delete: " . count($orphanFiles) . "\n";
        echo "Successfully deleted: {$deletedCount}\n";
        echo "Failed to delete: {$failedCount}\n";
        echo "Total space freed: " . round($deletedSize / 1024 / 1024, 2) . " MB\n";
        
        // Clean empty directories
        if (!$dryRun) {
            $this->cleanEmptyDirectories($this->uploadPath);
        }
    }
    
    /**
     * Clean empty directories
     */
    private function cleanEmptyDirectories($path)
    {
        if (!is_dir($path)) {
            return;
        }
        
        $files = scandir($path);
        $hasFiles = false;
        
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $fullPath = $path . DIRECTORY_SEPARATOR . $file;
                if (is_dir($fullPath)) {
                    $this->cleanEmptyDirectories($fullPath);
                    if (count(scandir($fullPath)) == 2) { // Only . and ..
                        rmdir($fullPath);
                        echo "[DIR REMOVED] {$fullPath}\n";
                    }
                } else {
                    $hasFiles = true;
                }
            }
        }
    }
    
    /**
     * Generate report of file types
     */
    public function generateReport()
    {
        $orphanFiles = $this->findOrphanFiles();
        
        $fileTypes = [];
        foreach ($orphanFiles as $file) {
            $ext = pathinfo($file['path'], PATHINFO_EXTENSION);
            if (!isset($fileTypes[$ext])) {
                $fileTypes[$ext] = [
                    'count' => 0,
                    'size' => 0
                ];
            }
            $fileTypes[$ext]['count']++;
            $fileTypes[$ext]['size'] += $file['size'];
        }
        
        echo "\n=== ORPHAN FILES BY TYPE ===\n";
        echo str_pad("Extension", 15) . str_pad("Count", 10) . str_pad("Size (MB)", 15) . "\n";
        echo str_repeat("-", 40) . "\n";
        
        foreach ($fileTypes as $ext => $data) {
            echo str_pad($ext ?: 'no extension', 15) . 
                 str_pad($data['count'], 10) . 
                 str_pad(round($data['size'] / 1024 / 1024, 2), 15) . "\n";
        }
        
        echo "\n=== LARGEST ORPHAN FILES ===\n";
        usort($orphanFiles, function($a, $b) {
            return $b['size'] - $a['size'];
        });
        
        $top10 = array_slice($orphanFiles, 0, 10);
        foreach ($top10 as $file) {
            echo str_pad($file['path'], 60) . " - " . $file['size_mb'] . " MB\n";
        }
    }
}

// Run the script
if (php_sapi_name() === 'cli') {
    $cleanup = new CleanupOrphanFiles();
    
    echo "\n========================================\n";
    echo "ORPHAN FILES CLEANUP UTILITY\n";
    echo "========================================\n\n";
    
    echo "Options:\n";
    echo "  1. Find orphan files (dry run)\n";
    echo "  2. Delete orphan files (actual delete)\n";
    echo "  3. Generate report\n";
    echo "  4. Exit\n\n";
    
    echo "Enter your choice (1-4): ";
    $handle = fopen("php://stdin", "r");
    $choice = trim(fgets($handle));
    
    switch ($choice) {
        case '1':
            $cleanup->findOrphanFiles();
            break;
        case '2':
            echo "\nWARNING: This will permanently delete orphan files!\n";
            echo "Type 'YES' to confirm: ";
            $confirm = trim(fgets($handle));
            if ($confirm === 'YES') {
                $cleanup->deleteOrphanFiles(false);
            } else {
                echo "Operation cancelled.\n";
            }
            break;
        case '3':
            $cleanup->generateReport();
            break;
        default:
            echo "Exiting...\n";
            break;
    }
    
    fclose($handle);
} else {
    echo "This script must be run from command line.\n";
}
?>