<?php

// Developer : Azozz ALFiras

use Exception;

class GoogleDriveService
{
    /**
     * Format bytes to a human-readable size.
     *
     * @param int $bytes
     * @param int $decimals
     * @return string
     */
    public function formatSize(int $bytes, int $decimals = 2): string
    {
        $size = ["B", "KB", "MB", "GB", "TB"];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f %sB", $bytes / pow(1024, $factor), $size[$factor]);
    }

    /**
     * Download file from Google Drive and save it locally.
     *
     * @param string $url
     * @param int $maxRetries
     * @return array
     * @throws Exception
     */
    public function downloadFile(string $url, int $maxRetries = 3): array
    {
        // Validate URL
        if (!preg_match('/drive\.google/i', $url)) {
            throw new Exception('Invalid URL');
        }

        // Extract file ID from URL
        preg_match('/(?:id=|\/d\/)([^&\/]+)/i', $url, $matches);
        if (empty($matches[1])) {
            throw new Exception('File ID not found');
        }

        $id = $matches[1];
        $endpoint = "https://drive.usercontent.google.com/u/0/uc?id={$id}&authuser=0&export=download";

        // Retry logic for downloading the file
        $fileContent = false;
        $retries = 0;
        $downloadUrl = null;

        while ($retries < $maxRetries && !$fileContent) {
            $fileContent = $this->makeHttpRequest($endpoint);
            $retries++;

            // Try to extract the download URL from the content
            $downloadUrl = $this->extractDownloadUrl($fileContent);
            if ($downloadUrl) {
                break;
            }
        }

        if (!$fileContent) {
            throw new Exception('Failed to download file after ' . $maxRetries . ' attempts');
        }

        // Extract file name from response headers
        $headers = get_headers($endpoint, 1);
        $contentDisposition = $headers['Content-Disposition'] ?? '';
        preg_match('/filename="([^"]+)"/', $contentDisposition, $filenameMatches);
        $fileName = $filenameMatches[1] ?? 'downloaded_file';

        // Save file to storage
        $path = $this->saveToFileSystem($fileName, $fileContent);
        if (!$path) {
            throw new Exception('Failed to save file to storage');
        }

        return [
            'download_url' => $downloadUrl,
            'name' => $fileName,
            'path' => $path
        ];
    }

    /**
     * Make an HTTP request using cURL.
     *
     * @param string $url
     * @return string|false
     */
    private function makeHttpRequest(string $url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Follow redirects
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        }

        curl_close($ch);
        return $response;
    }

    /**
     * Save file content to the file system.
     *
     * @param string $fileName
     * @param string $fileContent
     * @return string|false
     */
    private function saveToFileSystem(string $fileName, string $fileContent)
    {
        $path = __DIR__ . "/downloads/{$fileName}"; // Save in a "downloads" directory
        if (!file_exists(__DIR__ . '/downloads')) {
            mkdir(__DIR__ . '/downloads', 0777, true); // Create directory if it doesn't exist
        }

        return file_put_contents($path, $fileContent) ? $path : false;
    }

    /**
     * Extract download URL from HTML content.
     *
     * @param string $htmlContent
     * @return array|null
     */
    public function extractDownloadUrl(string $htmlContent): ?array
    {
        // Extract the necessary parameters from the HTML content
        preg_match('/<form[^>]+action="([^"]+)"/', $htmlContent, $formActionMatch);
        preg_match('/<input type="hidden" name="id" value="([^"]+)"/', $htmlContent, $idMatch);
        preg_match('/<input type="hidden" name="export" value="([^"]+)"/', $htmlContent, $exportMatch);
        preg_match('/<input type="hidden" name="confirm" value="([^"]+)"/', $htmlContent, $confirmMatch);
        preg_match('/<input type="hidden" name="uuid" value="([^"]+)"/', $htmlContent, $uuidMatch);
        preg_match('/<input type="hidden" name="at" value="([^"]+)"/', $htmlContent, $atMatch);

        // Extract the file extension
        preg_match('/<a [^>]*href="[^"]*"[^>]*>([^<]+)<\/a>/', $htmlContent, $filenameMatch);
        $extension = $filenameMatch ? '.' . pathinfo($filenameMatch[1], PATHINFO_EXTENSION) : '.zip';

        // Build the download URL if necessary parameters are found
        if (!empty($formActionMatch) && !empty($idMatch) && !empty($exportMatch) && !empty($confirmMatch) && !empty($uuidMatch)) {
            $downloadUrl = $formActionMatch[1] . '?id=' . $idMatch[1] . '&export=' . $exportMatch[1] . '&confirm=' . $confirmMatch[1] . '&uuid=' . $uuidMatch[1];
            
            // If 'at' parameter is present, include it in the URL
            if (!empty($atMatch[1])) {
                $downloadUrl .= '&at=' . $atMatch[1];
                return ['download_url' => $downloadUrl, 'extension' => $extension];
            }

            // If 'at' is missing, request it
            return $this->requestAtAndBuildUrl($downloadUrl, $extension);
        }

        return null;
    }

    /**
     * Request the 'at' parameter and build the download URL.
     *
     * @param string $downloadUrl
     * @param string $extension
     * @return array
     */
    public function requestAtAndBuildUrl(string $downloadUrl, string $extension): array
    {
        try {
            $response = $this->makeHttpRequest($downloadUrl);

            // Save the file to storage
            $fileName = 'downloaded_file_' . time() . $extension;
            $path = $this->saveToFileSystem($fileName, $response);

            if ($path) {
                return [
                    'message' => 'File downloaded and saved successfully.',
                    'file_path' => $path
                ];
            }

            throw new Exception("Failed to save the file on the server.");
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Generate the 'at' parameter used in Google Drive's download URL.
     *
     * @return string
     */
    public function generateAtParameter(): string
    {
        $timestamp = time();
        $uuid = uniqid();
        $data = $timestamp . ':' . $uuid;
        return base64_encode($data);
    }
}