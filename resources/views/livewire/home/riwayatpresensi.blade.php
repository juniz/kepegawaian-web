<?php

use Livewire\Volt\Component;
use App\Models\TemporaryPresensi;
use App\Models\RekapPresensi;
use Livewire\WithPagination;
use Livewire\Attributes\On; 
use Illuminate\Support\Facades\DB;

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
            ['key' => 'lokasi', 'label' => 'Lokasi'],
        ];
    }

    public function sortBy()
    {
        return [
            'column' => 'jam_datang',
            'direction' => 'desc',
        ];
    }

    public function getGeo($id)
    {
        // dd($id);
        $data = DB::table('geolocation_presensi')
            ->where('id', $id)
            ->where('tanggal', date('Y-m-d'))
            ->first();
        return $data->latitude.', '.$data->longitude;
    }

    #[On('refresh')]
    public function presensi()
    {
        $rekap = TemporaryPresensi::query()
            ->join('pegawai', 'temporary_presensi.id', '=', 'pegawai.id')
            ->join('departemen', 'pegawai.departemen', '=', 'departemen.dep_id')
            ->where('departemen.dep_id', session('user')->cap)
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
        <x-table :headers="$headers" :rows="$presensi" striped with-pagination >
            @scope('cell_lokasi', $value)
                @php
                    $geo = $this->getGeo($value->id);
                @endphp
                <x-button class="btn-sm" link="https://www.google.com/maps/search/?api=1&query={{ $geo }}" external icon="o-map" tooltip="{{$geo}}" />
            @endscope
        </x-table>
    </x-card>
</div>
