<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ProductImageService
{
    public const MAX_DIMENSION = 1200;
    public const JPEG_QUALITY  = 92;

    /**
     * Process an uploaded product image:
     * - NO CROPPING: Preserves 100% of the original photo aspect ratio and pixels.
     * - Never cuts off any part of the image.
     * - Optimizes resolution: downscales huge files to max 1200px, upscales small files to at least 800px.
     * - Fixes orientation from EXIF metadata.
     * - Saves as optimized, high-quality JPEG.
     */
    public static function processAndSave(
        UploadedFile|string $file,
        ?string $customFilename = null,
        int $maxDim = self::MAX_DIMENSION,
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

        // Fallback if GD is missing
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
                if ($file instanceof UploadedFile) {
                    $file->move($uploadDir, $filename);
                } else {
                    @copy($sourcePath, $destinationPath);
                }
                return $filename;
            }

            // Fix orientation from EXIF
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

            // ZERO CROPPING: Keep exact original aspect ratio
            $maxSrc = max($srcW, $srcH);
            $scale = 1.0;

            if ($maxSrc > $maxDim) {
                $scale = $maxDim / $maxSrc;
            } elseif ($maxSrc < 600) {
                $scale = 800 / $maxSrc;
            }

            $targetW = (int) max(1, round($srcW * $scale));
            $targetH = (int) max(1, round($srcH * $scale));

            $canvas = imagecreatetruecolor($targetW, $targetH);

            // Handle transparency for PNG/WebP: fill canvas with pure white
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);

            imagecopyresampled(
                $canvas,
                $srcImage,
                0,
                0,
                0,
                0,
                $targetW,
                $targetH,
                $srcW,
                $srcH
            );

            // Save high-quality JPEG
            imagejpeg($canvas, $destinationPath, $quality);

            imagedestroy($srcImage);
            imagedestroy($canvas);

            return $filename;
        } catch (\Throwable $e) {
            Log::warning('ProductImageService failed to process image: ' . $e->getMessage());

            if ($file instanceof UploadedFile) {
                $file->move($uploadDir, $filename);
            } else {
                @copy($sourcePath, $destinationPath);
            }
            return $filename;
        }
    }

    /**
     * Reprocess an existing image file on disk in-place without cropping.
     */
    public static function reprocessInPlace(
        string $filePath,
        int $maxDim = self::MAX_DIMENSION,
        int $quality = self::JPEG_QUALITY
    ): bool {
        if (!file_exists($filePath)) {
            return false;
        }

        $tempPath = $filePath . '.tmp.jpg';
        try {
            self::processAndSave($filePath, basename($tempPath), $maxDim, $quality);
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
     * Fix orientation from EXIF metadata.
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
