<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\QrGeneratorService;
use App\Services\EntitlementService;
use App\Models\Activity;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Storage;

class QrGenerator extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    // Content Type Selection
    public $contentType = 'url'; // 'url', 'text', 'whatsapp', 'wifi', 'email', 'vcard'

    // Form Inputs
    public $url = '';
    public $text = '';
    public $waPhone = '';
    public $waMessage = '';
    public $wifiSsid = '';
    public $wifiPassword = '';
    public $wifiEncryption = 'WPA';
    public $emailTo = '';
    public $emailSubject = '';
    public $emailBody = '';
    public $vcardName = '';
    public $vcardOrg = '';
    public $vcardPhone = '';
    public $vcardEmail = '';

    // Style Customizations
    public $dotStyle = 'round'; // 'round' or 'square'
    public $size = 'medium'; // 'small', 'medium', 'large'
    public $format = 'png'; // 'png' or 'svg'
    public $logo;

    // Live Preview State
    public $previewUri;

    // Output Result State
    public $resultPath;
    public $resultFilename;
    public $resultSize;
    public $resultFormat;
    public $resultDataUri;
    public $errorMsg;

    public function mount(QrGeneratorService $service)
    {
        $this->updatePreview($service);
    }

    public function setContentType(string $type, QrGeneratorService $service)
    {
        $this->contentType = $type;
        $this->resetResult();
        $this->updatePreview($service);
    }

    public function updated($property, $value)
    {
        if ($property === 'logo' && $this->logo) {
            $this->format = 'png';
        }

        if (in_array($property, [
            'contentType', 'url', 'text', 'waPhone', 'waMessage',
            'wifiSsid', 'wifiPassword', 'wifiEncryption',
            'emailTo', 'emailSubject', 'emailBody',
            'vcardName', 'vcardOrg', 'vcardPhone', 'vcardEmail',
            'dotStyle', 'size', 'format', 'logo'
        ])) {
            $service = app(QrGeneratorService::class);
            $this->updatePreview($service);
        }
    }

    public function removeLogo()
    {
        $this->logo = null;
        $service = app(QrGeneratorService::class);
        $this->updatePreview($service);
    }

    public function updatePreview(QrGeneratorService $service)
    {
        $payload = $this->buildPayload($service);
        if (empty(trim($payload))) {
            $payload = 'https://example.com';
        }

        $options = [
            'dot_style' => $this->dotStyle,
            'has_logo' => (bool)$this->logo,
        ];

        try {
            $this->previewUri = $service->renderPreview($payload, $options, $this->logo);
        } catch (\Throwable $e) {
            // Keep previous preview if generation fails temporarily while typing
        }
    }

    protected function buildPayload(QrGeneratorService $service): string
    {
        $data = [
            'url' => $this->url,
            'text' => $this->text,
            'wa_phone' => $this->waPhone,
            'wa_message' => $this->waMessage,
            'wifi_ssid' => $this->wifiSsid,
            'wifi_encryption' => $this->wifiEncryption,
            'wifi_password' => $this->wifiPassword,
            'email_to' => $this->emailTo,
            'email_subject' => $this->emailSubject,
            'email_body' => $this->emailBody,
            'vcard_name' => $this->vcardName,
            'vcard_org' => $this->vcardOrg,
            'vcard_phone' => $this->vcardPhone,
            'vcard_email' => $this->vcardEmail,
        ];

        return $service->formatPayload($this->contentType, $data);
    }

    public function generate(QrGeneratorService $service, EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'qr-generator';

        // 1. Quota check
        $remaining = $user ? $entitlementService->getRemainingQuota($user, $toolSlug) : null;
        if ($remaining !== null && $remaining <= 0) {
            $this->errorMsg = 'Kuota bulanan Anda sudah habis. Silakan upgrade ke paket Pro untuk akses tanpa batas.';
            $this->toast('Kuota bulanan kamu sudah habis.', 'info');
            return;
        }

        $payload = $this->buildPayload($service);
        if (empty(trim($payload))) {
            $this->errorMsg = 'Silakan masukkan konten atau data terlebih dahulu sebelum membuat QR Code.';
            $this->toast('Konten QR Code tidak boleh kosong.', 'warning');
            return;
        }

        if ($this->logo) {
            $this->validate([
                'logo' => 'image|mimes:png,jpg,jpeg,webp|max:5120',
            ], [
                'logo.image' => 'File logo harus berupa gambar (PNG/JPG/WebP).',
                'logo.max' => 'Ukuran logo maksimal 5MB.',
            ]);
        }

        try {
            $activity = Activity::create([
                'user_id' => auth()->id(),
                'tool_slug' => $toolSlug,
                'original_filename' => 'qrcode_' . $this->contentType . '.' . $this->format,
                'original_size' => strlen($payload),
                'status' => 'processing',
            ]);

            $options = [
                'format' => $this->format,
                'size' => $this->size,
                'dot_style' => $this->dotStyle,
            ];

            $result = $service->generate($payload, $options, $this->logo);

            $this->resultPath = $result['result_path'];
            $this->resultFilename = $result['result_filename'];
            $this->resultSize = $result['size'];
            $this->resultFormat = $result['format'];
            $this->resultDataUri = $result['data_uri'];

            $activity->update([
                'result_filename' => $this->resultFilename,
                'result_size' => $this->resultSize,
                'result_path' => $this->resultPath,
                'status' => 'completed',
                'meta' => [
                    'content_type' => $this->contentType,
                    'format' => $this->resultFormat,
                    'size' => $this->size,
                ],
            ]);

            \App\Models\Tool::incrementUsage($toolSlug);

            $this->toast('QR Code berhasil di-generate!', 'success');

        } catch (\Throwable $e) {
            if (isset($activity)) {
                $activity->update([
                    'status' => 'failed',
                    'meta' => ['error' => $e->getMessage()]
                ]);
            }
            $cleanMsg = explode("\n", trim($e->getMessage()))[0] ?? 'Terjadi kesalahan sistem.';
            $this->errorMsg = 'Gagal membuat QR Code: ' . $cleanMsg;
            $this->toast('Proses gagal: ' . $cleanMsg, 'error');
        }
    }

    public function resetResult()
    {
        $this->resultPath = null;
        $this->resultFilename = null;
        $this->resultSize = null;
        $this->resultFormat = null;
        $this->resultDataUri = null;
        $this->errorMsg = null;
    }

    public function download()
    {
        if ($this->resultPath && Storage::disk('local')->exists($this->resultPath)) {
            $filename = $this->resultFilename ?: ('qrcode_' . time() . '.' . $this->format);
            return Storage::disk('local')->download($this->resultPath, $filename);
        }

        $this->errorMsg = 'File hasil tidak ditemukan atau sesi telah berakhir.';
        $this->toast('File tidak ditemukan.', 'error');
    }

    public function render(EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'qr-generator';

        $remainingQuota = $user ? $entitlementService->getRemainingQuota($user, $toolSlug) : null;
        $currentPlan = $user ? $entitlementService->getCurrentPlan($user) : \App\Models\Plan::where('is_default', true)->first();
        $monthlyLimit = $currentPlan->limits[$toolSlug]['monthly_quota'] ?? $currentPlan->limits[$toolSlug]['daily_quota'] ?? 15;
        $isPro = $user && $user->isSubscribed();

        return view('livewire.tools.qr-generator', [
            'remainingQuota' => $remainingQuota,
            'monthlyLimit' => $monthlyLimit,
            'isPro' => $isPro,
            'currentPlan' => $currentPlan,
            'errorMsg' => $this->errorMsg,
        ]);
    }
}
