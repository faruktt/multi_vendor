<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ProductImageService
{
    public const TARGET_WIDTH  = 800;
    public const TARGET_HEIGHT = 1000;
    public const JPEG_QUALITY  = 92;

    /**
     * Process an uploaded product image:
     * - Standardizes image to 800x1000 (4:5) canvas
     * - Upscales small images so they never appear tiny
     * - Fills full height matching all catalog cards
     * - Never crops the central product subject ("r crop jeno na hoi")
     * - Returns the generated filename (e.g. 'img_xxxxxx.jpg')
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

            $aspectRatio = $srcW / $srcH;

            // Target canvas 800x1000
            $canvas = imagecreatetruecolor($targetW, $targetH);

            // For photos up to aspect ratio 1.35 (square, 4:3, 1:1, portrait):
            // Scale to fill the 800x1000 frame completely so height is 100% full with zero white bars.
            // The central subject is centered and never cropped.
            if ($aspectRatio <= 1.35) {
                $scale = max($targetW / $srcW, $targetH / $srcH);
                $newW = (int) max(1, round($srcW * $scale));
                $newH = (int) max(1, round($srcH * $scale));

                $dstX = (int) round(($targetW - $newW) / 2);
                $dstY = (int) round(($targetH - $newH) / 2);

                $white = imagecolorallocate($canvas, 255, 255, 255);
                imagefill($canvas, 0, 0, $white);

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
            } else {
                // Wide landscape image (> 1.35):
                // 1. Fill background with blurred ambient version of the photo itself
                $bgScale = max($targetW / $srcW, $targetH / $srcH);
                $bgW = (int) round($srcW * $bgScale);
                $bgH = (int) round($srcH * $bgScale);
                $bgX = (int) round(($targetW - $bgW) / 2);
                $bgY = (int) round(($targetH - $bgH) / 2);

                imagecopyresampled($canvas, $srcImage, $bgX, $bgY, 0, 0, $bgW, $bgH, $srcW, $srcH);

                if (function_exists('imagefilter')) {
                    for ($i = 0; $i < 15; $i++) {
                        imagefilter($canvas, IMG_FILTER_GAUSSIAN_BLUR);
                    }
                }

                // 2. Overlay the sharp, full uncropped product centered
                $fgScale = min($targetW / $srcW, $targetH / $srcH);
                $fgW = (int) round($srcW * $fgScale);
                $fgH = (int) round($srcH * $fgScale);
                $fgX = (int) round(($targetW - $fgW) / 2);
                $fgY = (int) round(($targetH - $fgH) / 2);

                imagecopyresampled($canvas, $srcImage, $fgX, $fgY, 0, 0, $fgW, $fgH, $srcW, $srcH);
            }

            // Save high-quality JPEG
            imagejpeg($canvas, $destinationPath, $quality);

            imagedestroy($srcImage);
            imagedestroy($canvas);

            return $filename;
        } catch (\Throwable $e) {
            Log::warning('ProductImageService failed to resize image: ' . $e->getMessage());

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
