<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public $nik = '';
    public $id_pegawai = '';

    public function mount()
    {
        $user = session('user');
        $this->id_pegawai = $user->id;
        $this->nik = $this->getNik();
    }

    public function getNik()
    {
        $nik = DB::table('pegawai')
            ->where('id', session('user')->id)
            ->select('nik')
            ->first();
        return $nik->nik;
    }

    public function getJmlIzin()
    {
        $izin = DB::table('pengajuan_izin')
            ->where('nik', $this->nik)
            ->where('status', 'Disetujui')
            ->where('tanggal_awal', 'like', date('Y-').'%')
            ->count();
        return $izin;
    }

    public function getJmlCuti()
    {
        $cuti = DB::table('pengajuan_cuti')
            ->where('nik', $this->nik)
            ->where('status', 'Disetujui')
            ->where('tanggal_awal', 'like', date('Y-').'%')
            ->count();
        return $cuti;
    }

    public function getJmlAbsensi()
    {
        $absensi = DB::table('rekap_presensi')
            ->where('id', $this->id_pegawai)
            ->where('jam_datang', 'like', date('Y-').'%')
            ->count();
        return $absensi;
    }

    public function getJmlTelatAbsensi()
    {
        $absensi = DB::table('rekap_presensi')
            ->where('id', $this->id_pegawai)
            ->where('jam_datang', 'like', date('Y-').'%')
            ->where('status', 'like', 'Terlambat%')
            ->count();
        return $absensi;
    }

    public function with(): array
    {
        return [
            'nik' => $this->nik,
            'jmlIzin' => $this->getJmlIzin(),
            'jmlCuti' => $this->getJmlCuti(),
            'jmlAbsensi' => $this->getJmlAbsensi(),
            'jmlTelatAbsensi' => $this->getJmlTelatAbsensi(),
        ];
    }

}; ?>

<div>
    
    <x-header title="Dashboard" separator />
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-4">
        <x-stat title="Izin" description="Tahun ini" value="{{$jmlIzin}}" icon="o-envelope" tooltip="Jumlah Izin Tahun Ini" /> 
        <x-stat
            title="Cuti"
            description="Tahun ini"
            value="{{$jmlCuti}}"
            icon="o-clipboard"
            tooltip="Jumlah Cuti Tahun Ini" />
        
        <x-stat
            title="Absensi"
            description="Tahun ini"
            value="{{$jmlAbsensi}}"
            icon="o-user"
            tooltip="Jumlah Absensi Tahun Ini" />
        
        <x-stat
            title="Telat Absen"
            description="Tahun ini"
            value="{{$jmlTelatAbsensi}}"
            icon="o-arrow-trending-down"
            class="text-red-500"
            color="text-red-500"
            tooltip-right="Jumlah Telat Absensi Tahun Ini" />
    </div>
    <livewire:dashboard.chart />
</div>

@section('floating')
    <x-button icon='o-camera' class="btn-primary" link="/home">
        Presensi
    </x-button>
@endsection
