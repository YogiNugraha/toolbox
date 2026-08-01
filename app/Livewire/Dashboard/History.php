<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Activity;

class History extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function export()
    {
        $activities = Activity::where('user_id', auth()->id())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('tool_slug', 'like', '%' . $this->search . '%')
                      ->orWhere('original_filename', 'like', '%' . $this->search . '%')
                      ->orWhere('status', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->get();

        $tempPath = storage_path('framework/cache/spout');
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        return response()->streamDownload(function () use ($tempPath, $activities) {
            $options = new \OpenSpout\Writer\XLSX\Options();
            $options->setTempFolder($tempPath);

            $writer = new \OpenSpout\Writer\XLSX\Writer($options);
            $writer->openToFile('php://output');

            // Add Header
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Tool', 'Detail File', 'Ukuran Asli', 'Ukuran Hasil', 'Status', 'Tanggal'
            ]));

            foreach ($activities as $activity) {
                $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                    \Illuminate\Support\Str::headline($activity->tool_slug),
                    $activity->original_filename,
                    $activity->original_size ?? 0,
                    $activity->result_size ?? 0,
                    strtoupper($activity->status),
                    $activity->created_at->format('Y-m-d H:i:s'),
                ]));
            }

            $writer->close();
        }, 'riwayat_aktivitas.xlsx');
    }

    public function render()
    {
        $activities = Activity::where('user_id', auth()->id())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('tool_slug', 'like', '%' . $this->search . '%')
                      ->orWhere('original_filename', 'like', '%' . $this->search . '%')
                      ->orWhere('status', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.dashboard.history', [
            'activities' => $activities
        ])->layout('layouts.dashboard');
    }
}
