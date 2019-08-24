<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Service;

use Sitegeist\FluidStyleguide\Domain\Model\Component;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class ComponentDownloadService
{
    public function downloadZip(Component $component): void
    {
        $componentPath = $component->getLocation()->getDirectory();
        $realFileName = $component->getName()->getSimpleName() .'.zip';
        $componentPath = PathUtility::sanitizeTrailingSeparator($componentPath);
        $temporaryPath = Environment::getVarPath() . '/transient/';
        if (!@is_dir($temporaryPath)) {
            GeneralUtility::mkdir($temporaryPath);
        }
        $fileName = $temporaryPath . 'component_'
            . md5($component->getName()->getIdentifier())
            . '_' . bin2hex(random_bytes(16)) . '.zip';
        $temporaryPath = Environment::getVarPath() . '/transient/';
        if (!@is_dir($temporaryPath)) {
            GeneralUtility::mkdir($temporaryPath);
        }
        $zip = new \ZipArchive();
        $zip->open($fileName, \ZipArchive::CREATE);
        // Get all the files of the extension, but exclude the ones specified in the excludePattern
        $files = GeneralUtility::getAllFilesAndFoldersInPath(
            [], // No files pre-added
            $componentPath, // Start from here
            '', // Do not filter files by extension
            true, // Include subdirectories
            PHP_INT_MAX, // Recursion level
            false        // Files and directories to exclude.
        );
        // Make paths relative to extension root directory.
        $files = GeneralUtility::removePrefixPathFromList($files, $componentPath);
        // Remove the one empty path that is the extension dir itself.
        $files = array_filter($files);
        foreach ($files as $file) {
            $fullPath = $componentPath . $file;
            // Distinguish between files and directories, as creation of the archive
            // fails on Windows when trying to add a directory with "addFile".
            if (is_dir($fullPath)) {
                $zip->addEmptyDir($file);
            } else {
                $zip->addFile($fullPath, $file);
            }
        }
        $zip->close();

        if (file_exists($fileName)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($realFileName) . '"');
            header('Content-Length: ' . filesize($fileName));
            flush();
            readfile($fileName);
            unlink($fileName);
            exit;
        }
    }
}
