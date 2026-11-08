<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ProductImageService
{
    public const TARGET_WIDTH  = 800;
    public const TARGET_HEIGHT = 1000;
    public const JPEG_QUALITY  = 90;

    /**
     * Process an uploaded product image:
     * - Resizes to fit nicely inside the standard 800x1000 (4:5) canvas
     * - Upscales small images so they never appear tiny
     * - Centers the image on a clean white background
     * - Never crops any part of the product ("r crop jeno na hoi")
     * - Returns the generated filename (e.g. 'img_xxxxxx.jpg')
     *
     * @param UploadedFile|string $file
     * @param string|null $customFilename
     * @param int $targetW
     * @param int $targetH
     * @param int $quality
     * @return string
     */
    public static function processAndSave(
        UploadedFile|string $file,
        ?string $customFilename = null,
        int $targetW = self::TARGET_WIDTH,
        int $targetH = self::TARGET_HEIGHT,
        int $quality = self::JPEG_QUALITY
    ): string {
        $uploadDir = public_path('uploads/products');
        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $filename = $customFilename ?: (uniqid('img_', true) . '.jpg');
        $destinationPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        // Resolve source path
        $sourcePath = ($file instanceof UploadedFile) ? $file->getRealPath() : $file;

        if (!file_exists($sourcePath)) {
            throw new \InvalidArgumentException("Source image does not exist: {$sourcePath}");
        }

        // If GD is unavailable, fallback to direct copy/move
        if (!function_exists('imagecreatetruecolor')) {
            if ($file instanceof UploadedFile) {
                $file->move($uploadDir, $filename);
            } else {
                @copy($sourcePath, $destinationPath);
            }
            return $filename;
        }

        try {
            $srcImage = self::createImageResource($sourcePath);
            if (!$srcImage) {
                // Fallback to moving as-is
                if ($file instanceof UploadedFile) {
                    $file->move($uploadDir, $filename);
                } else {
                    @copy($sourcePath, $destinationPath);
                }
                return $filename;
            }

            // Auto-rotate according to EXIF if available
            $srcImage = self::fixOrientation($srcImage, $sourcePath);

            $srcW = imagesx($srcImage);
            $srcH = imagesy($srcImage);

            if ($srcW <= 0 || $srcH <= 0) {
                imagedestroy($srcImage);
                if ($file instanceof UploadedFile) {
                    $file->move($uploadDir, $filename);
                } else {
                    @copy($sourcePath, $destinationPath);
                }
                return $filename;
            }

            // Proportional scaling to fit completely within target canvas without cropping
            $scale = min($targetW / $srcW, $targetH / $srcH);
            $newW = (int) max(1, round($srcW * $scale));
            $newH = (int) max(1, round($srcH * $scale));

            // Center image on target canvas
            $dstX = (int) round(($targetW - $newW) / 2);
            $dstY = (int) round(($targetH - $newH) / 2);

            // Create target canvas
            $canvas = imagecreatetruecolor($targetW, $targetH);

            // Fill with pure white (#FFFFFF) background
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);

            // High-quality bicubic resampling
            imagecopyresampled(
                $canvas,
                $srcImage,
                $dstX,
                $dstY,
                0,
                0,
                $newW,
                $newH,
                $srcW,
                $srcH
            );

            // Save as high-quality JPEG
            imagejpeg($canvas, $destinationPath, $quality);

            imagedestroy($srcImage);
            imagedestroy($canvas);

            return $filename;
        } catch (\Throwable $e) {
            Log::warning('ProductImageService failed to resize image: ' . $e->getMessage());

            // Safe fallback
            if ($file instanceof UploadedFile) {
                $file->move($uploadDir, $filename);
            } else {
                @copy($sourcePath, $destinationPath);
            }
            return $filename;
        }
    }

    /**
     * Reprocess an existing image file on disk in-place.
     */
    public static function reprocessInPlace(
        string $filePath,
        int $targetW = self::TARGET_WIDTH,
        int $targetH = self::TARGET_HEIGHT,
        int $quality = self::JPEG_QUALITY
    ): bool {
        if (!file_exists($filePath)) {
            return false;
        }

        $tempPath = $filePath . '.tmp.jpg';
        try {
            self::processAndSave($filePath, basename($tempPath), $targetW, $targetH, $quality);
            if (file_exists($tempPath) && filesize($tempPath) > 0) {
                @unlink($filePath);
                @rename($tempPath, $filePath);
                return true;
            }
        } catch (\Throwable $e) {
            if (file_exists($tempPath)) @unlink($tempPath);
            Log::warning("reprocessInPlace error on {$filePath}: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Create GD resource from any supported format.
     */
    private static function createImageResource(string $path)
    {
        $info = @getimagesize($path);
        if (!$info) {
            return null;
        }

        return match ($info[2] ?? 0) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            IMAGETYPE_GIF  => @imagecreatefromgif($path),
            IMAGETYPE_BMP  => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($path) : null,
            default        => null,
        };
    }

    /**
     * Fix orientation from EXIF metadata (mobile phone uploads).
     */
    private static function fixOrientation($image, string $path)
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }

        try {
            $exif = @exif_read_data($path);
            if (!empty($exif['Orientation'])) {
                $image = match ($exif['Orientation']) {
                    3 => imagerotate($image, 180, 0),
                    6 => imagerotate($image, -90, 0),
                    8 => imagerotate($image, 90, 0),
                    default => $image,
                };
            }
        } catch (\Throwable) {
            // Ignore EXIF errors
        }

        return $image;
    }
}
