<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Activity;
use App\Traits\LivewireLineoneAlerts;

class History extends Component
{
    use WithPagination, LivewireLineoneAlerts;

    public $search = '';
    public $perPage = 10;
    public $statusFilter = '';
    public $toolFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingToolFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->toolFilter = '';
        $this->resetPage();
    }

    public function deleteActivity($id)
    {
        $activity = Activity::where('user_id', auth()->id())->findOrFail($id);
        if ($activity->result_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($activity->result_path)) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($activity->result_path);
        }
        $activity->delete();
        $this->toast('Aktivitas berhasil dihapus.', 'success');
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
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->toolFilter, function ($query) {
                $query->where('tool_slug', $this->toolFilter);
            })
            ->latest()
            ->get();

        $tempPath = storage_path('framework/cache/spout');
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        $response = response()->streamDownload(function () use ($tempPath, $activities) {
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
        
        $this->toast('File Excel siap diunduh.', 'success');
        return $response;
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
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->toolFilter, function ($query) {
                $query->where('tool_slug', $this->toolFilter);
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.dashboard.history', [
            'activities' => $activities,
            'tools' => \App\Models\Tool::orderBy('name')->get(),
        ])->layout('layouts.dashboard');
    }
}
