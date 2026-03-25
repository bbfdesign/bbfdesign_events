<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Service;

use Plugin\bbfdesign_events\src\Config\EventConfig;

class MediaService
{
    public function upload(array $file, string $context = 'images'): ?array
    {
        $targetDir = EventConfig::getAbsoluteMediaPath($context);

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $mimeType = mime_content_type($file['tmp_name']);
        if (!in_array($mimeType, EventConfig::ALLOWED_UPLOAD_TYPES, true)) {
            return null;
        }

        if ($file['size'] > EventConfig::MAX_UPLOAD_SIZE) {
            return null;
        }

        $safeName = $this->generateSafeFilename($file['name']);
        $targetPath = $targetDir . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return null;
        }

        return [
            'filename' => $safeName,
            'url' => '/' . EventConfig::getMediaPath($context) . $safeName,
            'mime_type' => $mimeType,
            'size' => filesize($targetPath),
        ];
    }

    /**
     * @return array<int, array{filename: string, url: string, context: string}>
     */
    public function listFiles(?string $context = null): array
    {
        $assets = [];
        $dirs = $context !== null
            ? [$context => EventConfig::MEDIA_DIRS[$context] ?? 'images/']
            : EventConfig::MEDIA_DIRS;

        foreach ($dirs as $ctx => $subDir) {
            $dir = EventConfig::getAbsoluteMediaPath($ctx);
            if (!is_dir($dir)) {
                continue;
            }

            foreach (glob($dir . '*.*') as $file) {
                $filename = basename($file);
                $assets[] = [
                    'filename' => $filename,
                    'url' => '/' . EventConfig::getMediaPath($ctx) . $filename,
                    'context' => $ctx,
                ];
            }
        }

        return $assets;
    }

    public function delete(string $relativePath): bool
    {
        $absolutePath = \PFAD_ROOT . ltrim($relativePath, '/');
        if (is_file($absolutePath)) {
            return unlink($absolutePath);
        }
        return false;
    }

    private function generateSafeFilename(string $original): string
    {
        $info = pathinfo($original);
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '-', $info['filename']);
        $name = trim(preg_replace('/-+/', '-', $name), '-');
        $ext = strtolower($info['extension'] ?? 'jpg');

        return strtolower($name) . '-' . substr(md5(uniqid('', true)), 0, 6) . '.' . $ext;
    }
}
