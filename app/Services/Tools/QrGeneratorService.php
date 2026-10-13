<?php

namespace App\Services\Tools;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Output\QRMarkupSVG;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QrGeneratorService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Format payload based on selected QR content type.
     */
    public function formatPayload(string $type, array $data): string
    {
        return match ($type) {
            'url' => trim($data['url'] ?? ''),
            'whatsapp' => (function () use ($data) {
                $phone = preg_replace('/[^0-9]/', '', $data['wa_phone'] ?? '');
                $msg = urlencode($data['wa_message'] ?? '');
                return !empty($phone) ? "https://wa.me/{$phone}?text={$msg}" : '';
            })(),
            'wifi' => (function () use ($data) {
                $ssid = addslashes($data['wifi_ssid'] ?? '');
                $enc = $data['wifi_encryption'] ?? 'WPA';
                $pass = addslashes($data['wifi_password'] ?? '');
                return !empty($ssid) ? "WIFI:S:{$ssid};T:{$enc};P:{$pass};;" : '';
            })(),
            'email' => (function () use ($data) {
                $email = trim($data['email_to'] ?? '');
                $subj = urlencode($data['email_subject'] ?? '');
                $body = urlencode($data['email_body'] ?? '');
                return !empty($email) ? "mailto:{$email}?subject={$subj}&body={$body}" : '';
            })(),
            'vcard' => (function () use ($data) {
                $fn = trim($data['vcard_name'] ?? '');
                $org = trim($data['vcard_org'] ?? '');
                $tel = trim($data['vcard_phone'] ?? '');
                $email = trim($data['vcard_email'] ?? '');
                return !empty($fn) ? "BEGIN:VCARD\nVERSION:3.0\nFN:{$fn}\nORG:{$org}\nTEL;TYPE=CELL:{$tel}\nEMAIL:{$email}\nEND:VCARD" : '';
            })(),
            default => trim($data['text'] ?? ''),
        };
    }

    /**
     * Render fast lightweight base64 PNG data URI for instant live preview.
     */
    public function renderPreview(string $payload, array $options, mixed $logo = null): string
    {
        if (empty(trim($payload))) {
            $payload = 'https://example.com';
        }

        $scale = 8;
        $isCircular = !empty($options['dot_style']) && $options['dot_style'] === 'round';
        $hasLogo = $logo && method_exists($logo, 'getRealPath') && file_exists($logo->getRealPath());

        $qrOptions = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'outputBase64' => false,
            'scale' => $scale,
            'eccLevel' => $hasLogo ? EccLevel::H : EccLevel::M,
            'drawCircularModules' => $isCircular,
            'circleRadius' => 0.45,
            'bgColor' => [255, 255, 255],
            'imageTransparent' => false,
        ]);

        $qrcode = new QRCode($qrOptions);
        $rawPngBytes = $qrcode->render($payload);

        if ($hasLogo) {
            try {
                $qrImage = $this->imageManager->decodeBinary($rawPngBytes);
                $logoImage = $this->imageManager->decodePath($logo->getRealPath());

                $qrWidth = $qrImage->width();
                $targetLogoSize = (int) round($qrWidth * 0.22); // 22% of QR width

                $logoImage->scaleDown(width: $targetLogoSize, height: $targetLogoSize);

                // White rounded badge behind logo
                $badgeSize = $targetLogoSize + 12;
                $whiteBadge = $this->imageManager->createImage($badgeSize, $badgeSize)->fill('ffffff');
                $whiteBadge->insert($logoImage, alignment: 'center');

                $qrImage->insert($whiteBadge, alignment: 'center');
                $rawPngBytes = (string) $qrImage->encodeUsingFileExtension('png');
            } catch (\Throwable $e) {
                // If logo overlay fails, fallback to clean QR
            }
        }

        return 'data:image/png;base64,' . base64_encode($rawPngBytes);
    }

    /**
     * Generate final high-res QR code file (PNG or SVG) with optional center logo.
     */
    public function generate(string $payload, array $options, ?UploadedFile $logo = null): array
    {
        if (empty(trim($payload))) {
            $payload = 'https://example.com';
        }

        $format = strtolower($options['format'] ?? 'png'); // 'png' or 'svg'
        $scale = match ($options['size'] ?? 'medium') {
            'small' => 8,    // ~350px
            'large' => 20,   // ~900px
            default => 14,   // ~600px
        };

        $isCircular = !empty($options['dot_style']) && $options['dot_style'] === 'round';
        $hasLogo = ($logo !== null && $logo->isValid() && $format === 'png');

        if ($format === 'svg') {
            $qrOptions = new QROptions([
                'outputInterface' => QRMarkupSVG::class,
                'outputBase64' => false,
                'scale' => $scale,
                'eccLevel' => EccLevel::M,
                'svgUseFill' => true,
            ]);

            $qrcode = new QRCode($qrOptions);
            $svgContent = $qrcode->render($payload);

            $uniqueId = Str::uuid()->toString();
            $userId = auth()->id() ?? 'guest';
            $relativeDir = 'users/' . $userId;
            $outputFilename = 'qrcode_' . $uniqueId . '.svg';
            $relativeResultPath = $relativeDir . '/' . $outputFilename;

            Storage::disk('local')->makeDirectory($relativeDir);
            Storage::disk('local')->put($relativeResultPath, $svgContent);

            return [
                'result_path' => $relativeResultPath,
                'result_filename' => 'qrcode_' . time() . '.svg',
                'size' => strlen($svgContent),
                'format' => 'svg',
                'data_uri' => 'data:image/svg+xml;base64,' . base64_encode($svgContent),
            ];
        }

        // Generate high-res PNG
        $qrOptions = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'outputBase64' => false,
            'scale' => $scale,
            'eccLevel' => $hasLogo ? EccLevel::H : EccLevel::M,
            'drawCircularModules' => $isCircular,
            'circleRadius' => 0.45,
            'bgColor' => [255, 255, 255],
            'imageTransparent' => false,
        ]);

        $qrcode = new QRCode($qrOptions);
        $rawPngBytes = $qrcode->render($payload);

        // Stamp logo if uploaded
        if ($hasLogo) {
            $qrImage = $this->imageManager->decodeBinary($rawPngBytes);
            $logoImage = $this->imageManager->decodePath($logo->getRealPath());

            $qrWidth = $qrImage->width();
            $targetLogoSize = (int) round($qrWidth * 0.22); // 22% of QR width

            $logoImage->scaleDown(width: $targetLogoSize, height: $targetLogoSize);

            // Create white rounded background badge behind logo
            $badgeSize = $targetLogoSize + 16;
            $whiteBadge = $this->imageManager->createImage($badgeSize, $badgeSize)->fill('ffffff');
            $whiteBadge->insert($logoImage, alignment: 'center');

            $qrImage->insert($whiteBadge, alignment: 'center');
            $rawPngBytes = (string) $qrImage->encodeUsingFileExtension('png');
        }

        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'qrcode_' . $uniqueId . '.png';
        $relativeResultPath = $relativeDir . '/' . $outputFilename;

        Storage::disk('local')->makeDirectory($relativeDir);
        Storage::disk('local')->put($relativeResultPath, $rawPngBytes);

        return [
            'result_path' => $relativeResultPath,
            'result_filename' => 'qrcode_' . time() . '.png',
            'size' => strlen($rawPngBytes),
            'format' => 'png',
            'data_uri' => 'data:image/png;base64,' . base64_encode($rawPngBytes),
        ];
    }
}
