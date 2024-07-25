<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public $tanggal;
    public $departemen = '';
    public array $chartData = [];
    public bool $showTerlambat = false;
    public bool $showTepatWaktu = false;
    public bool $showUnit = false;
    public $units = [];
    public $dep = '';

    public function mount()
    {
        $user = session('user');
        $this->departemen = $user->cap;
        $this->tanggal = date('Y-m');
        $data = $this->absensiUnit($this->tanggal.'%')->toArray();
        // dd($data);
        $this->chartData = [
            'type' => 'bar',
            'data' => [
                'labels' => array_column($data, 'nama'),
                'datasets' => [
                    [
                        'label' => 'Tepat Waktu',
                        'data' => array_column($data, 'tepat_waktu'),
                        'backgroundColor' => 'green',
                        'borderColor' => 'green',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Toleransi',
                        'data' => array_column($data, 'toleransi'),
                        'backgroundColor' => 'yellow',
                        'borderColor' => 'yellow',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Terlambat I',
                        'data' => array_column($data, 'terlambat1'),
                        'backgroundColor' => 'orange',
                        'borderColor' => 'orange',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Terlambat II',
                        'data' => array_column($data, 'terlambat2'),
                        'backgroundColor' => 'red',
                        'borderColor' => 'red',
                        'borderWidth' => 1
                    ]
                ]
            ],
            'options' => [
                'indexAxis' => 'y',
                'responsive' => true,
                'aspectRatio' => 0.5,
            ]
        ];
        $this->units = $this->getUnit();
        // dd($this->units);
    }

    public function getUnit()
    {
        return DB::table('departemen')
                    ->selectRaw('dep_id as id, nama as name')
                    ->get();
    }
    
    public function updatedDep()
    {
        // dd($this->dep);
        $this->dispatch('getUnit', $this->tanggal, $this->dep);
    }

    public function absensiUnit($tanggal)
    {
        return DB::table('departemen')
                    ->where('departemen.dep_id', '<>', '-')
                    ->Where('departemen.dep_id', '<>', 'KA')
                    ->Where('departemen.dep_id', '<>', 'SPES')
                    ->Where('departemen.dep_id', '<>', 'POC')
                    ->Where('departemen.dep_id', '<>', 'VAKS')
                    ->selectRaw("departemen.dep_id, departemen.nama, 
                                IFNULL((SELECT COUNT(a.id) FROM rekap_presensi a 
                                JOIN pegawai b ON a.id = b.id WHERE b.departemen = departemen.dep_id 
                                AND a.`status` = 'Tepat Waktu' AND a.jam_datang LIKE '{$tanggal}' GROUP BY b.departemen),0) AS tepat_waktu, 
                                IFNULL((SELECT COUNT(a.id) FROM rekap_presensi a 
                                JOIN pegawai b ON a.id = b.id WHERE b.departemen = departemen.dep_id 
                                AND a.`status` = 'Terlambat Toleransi' AND a.jam_datang LIKE '{$tanggal}' GROUP BY b.departemen),0) AS toleransi, 
                                IFNULL((SELECT COUNT(a.id) FROM rekap_presensi a 
                                JOIN pegawai b ON a.id = b.id WHERE b.departemen = departemen.dep_id 
                                AND a.`status` = 'Terlambat I' AND a.jam_datang LIKE '{$tanggal}' GROUP BY b.departemen),0) AS terlambat1, 
                                IFNULL((SELECT COUNT(a.id) FROM rekap_presensi a 
                                JOIN pegawai b ON a.id = b.id WHERE b.departemen = departemen.dep_id 
                                AND a.`status` = 'Terlambat II' AND a.jam_datang LIKE '{$tanggal}' GROUP BY b.departemen),0) AS terlambat2")  
                    ->get();
    }

    public function openTerlambat()
    {
        $tanggal = $this->tanggal.'%';
        $this->dispatch('getTerlambat', $tanggal);
        $this->showTerlambat = true;
    }

    public function openTepatWaktu()
    {
        $tanggal = $this->tanggal.'%';
        $this->dispatch('getTepatWaktu', $tanggal);
        $this->showTepatWaktu = true;
    }

    public function openUnit()
    {
        $this->showUnit = true;
    }

    public function updatedTanggal()
    {
        $data = $this->absensiUnit($this->tanggal.'%')->toArray();
        // dd($data);
        $this->chartData = [
            'type' => 'bar',
            'data' => [
                'labels' => array_column($data, 'nama'),
                'datasets' => [
                    [
                        'label' => 'Tepat Waktu',
                        'data' => array_column($data, 'tepat_waktu'),
                        'backgroundColor' => 'green',
                        'borderColor' => 'green',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Toleransi',
                        'data' => array_column($data, 'toleransi'),
                        'backgroundColor' => 'yellow',
                        'borderColor' => 'yellow',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Terlambat I',
                        'data' => array_column($data, 'terlambat1'),
                        'backgroundColor' => 'orange',
                        'borderColor' => 'orange',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Terlambat II',
                        'data' => array_column($data, 'terlambat2'),
                        'backgroundColor' => 'red',
                        'borderColor' => 'red',
                        'borderWidth' => 1
                    ]
                ]
            ],
            'options' => [
                'indexAxis' => 'y',
                'responsive' => true,
                'aspectRatio' => 0.5,
            ]
        ];
    }

}; ?>

<div>
    <x-header title="Dashboard Pegawai" separator>
        <x-slot:middle class="!justify-end">
            <x-datetime wire:model.live="tanggal" icon="o-calendar" type="month" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label='Terlambat' class="btn-error" @click="$wire.openTerlambat"/>
            <x-button label='Tepat Waktu' class="btn-success" @click="$wire.openTepatWaktu" />
            <x-button label='Per Unit' class="btn-outline" @click="$wire.openUnit" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-chart wire:model="chartData" />
    </x-card>
    <x-drawer 
        wire:model="showTerlambat" 
        class="w-auto lg:w-1/2"
        title="Daftar Pegawai Terlambat"
        separator
        with-close-button
        close-on-escape
    >
        <div>
            <livewire:dashboard.terlambat />
        </div>
    </x-drawer>
    <x-drawer 
        wire:model="showTepatWaktu" 
        class="w-auto lg:w-1/2"
        title="Daftar Pegawai Tepat Waktu"
        separator
        with-close-button
        close-on-escape
    >
        <div>
            <livewire:dashboard.tepatwaktu />
        </div>
    </x-drawer>
    <x-drawer 
        wire:model="showUnit" 
        class="w-auto lg:w-1/2"
        title="Daftar Pegawai Per Unit"
        separator
        with-close-button
        close-on-escape
    >
        <div class="flex flex-col gap-4">
            <x-choices-offline
                wire:model.live='dep'
                :options='$units'
                icon='o-folder'
                placeholder='Pilih Unit'
                single
                searchable
            />
            <livewire:dashboard.unit />
        </div>
    </x-drawer>
</div>

@section('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endsection