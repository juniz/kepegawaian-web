<?php

use Livewire\Volt\Component;

new class extends Component {
    public array $chart = [
        'type' => 'line',
        'data' => [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => [
                [
                    'label' => 'Jumlah Absensi Telat Tahun Ini',
                    'data' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    'backgroundColor' => 'red',
                    'borderColor' => 'red',
                    'pointStyle' => 'circle',
                    'pointRadius' => 5,
                    'pointHoverRadius' => 15,   
                ]
            ]
        ]
    ];

    public function fillChart()
    {
        Arr::set($this->chart, 'data.datasets.0.data', [
            $this->getAbsensiTelat('01'),
            $this->getAbsensiTelat('02'),
            $this->getAbsensiTelat('03'),
            $this->getAbsensiTelat('04'),
            $this->getAbsensiTelat('05'),
            $this->getAbsensiTelat('06'),
            $this->getAbsensiTelat('07'),
            $this->getAbsensiTelat('08'),
            $this->getAbsensiTelat('09'),
            $this->getAbsensiTelat('10'),
            $this->getAbsensiTelat('11'),
            $this->getAbsensiTelat('12'),
        ]);
    }

    public function getAbsensiTelat($bulan)
    {
        $absensi = DB::table('rekap_presensi')
            ->where('id', session('user')->id)
            ->where('jam_datang', 'like', date('Y-').$bulan.'%')
            ->where('status', 'like', 'Terlambat%')
            ->count();
        return $absensi;
    }
}; ?>

<div wire:init='fillChart' class="mt-4">
    <x-card>
        <x-chart wire:model="chart" />
    </x-card>
</div>

@section('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endsection
