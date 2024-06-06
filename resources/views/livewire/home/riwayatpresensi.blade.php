<?php

use Livewire\Volt\Component;
use App\Models\TemporaryPresensi;
use App\Models\RekapPresensi;
use Livewire\WithPagination;
use Livewire\Attributes\On; 

new class extends Component {
    use WithPagination;
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'hidden'],
            ['key' => 'pegawai.nama', 'label' => 'Nama'],
            ['key' => 'shift', 'label' => 'Shift'],
            ['key' => 'jam_datang', 'label' => 'Jam Datang'],
            ['key' => 'jam_pulang', 'label' => 'Jam Pulang'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'keterlambatan', 'label' => 'Keterlambatan'],
        ];
    }

    public function sortBy()
    {
        return [
            'column' => 'jam_datang',
            'direction' => 'desc',
        ];
    }

    #[On('refresh')]
    public function presensi()
    {
        $rekap = TemporaryPresensi::query()
            ->join('pegawai', 'temporary_presensi.id', '=', 'pegawai.id')
            ->join('departemen', 'pegawai.departemen', '=', 'departemen.dep_id')
            ->where('departemen.dep_id', 'IT')
            ->orderBy('jam_datang', 'desc')
            ->paginate(10);

        return $rekap;
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'presensi' => $this->presensi(),
            'sortBy' => $this->sortBy(),
        ];
    }
}; ?>

<div class="space-y-2">
    <x-card>
        <x-table :headers="$headers" :rows="$presensi" striped with-pagination />
    </x-card>
</div>
