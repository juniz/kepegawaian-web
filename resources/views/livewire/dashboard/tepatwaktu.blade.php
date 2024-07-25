<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

new class extends Component {

    public $data = [];
    
    #[On('getTepatWaktu')]
    public function getData($tanggal)
    {
        $data = DB::table('pegawai')
                ->join("rekap_presensi", "pegawai.id", "=", "rekap_presensi.id")
                ->join("departemen", "departemen.dep_id", "=", "pegawai.departemen")
                ->whereRaw("pegawai.stts_aktif = 'AKTIF' AND
                    rekap_presensi.jam_datang LIKE '{$tanggal}' AND
                    IFNULL((SELECT COUNT(a.id) FROM rekap_presensi a 
                	JOIN pegawai b ON a.id = b.id WHERE b.id = pegawai.id AND a.jam_datang LIKE '{$tanggal}' GROUP BY b.id),0) =
                	IFNULL((SELECT COUNT(a.id) FROM rekap_presensi a 
                	JOIN pegawai b ON a.id = b.id WHERE b.id = pegawai.id AND a.jam_datang LIKE '{$tanggal}' AND a.status = 'Tepat Waktu' GROUP BY b.id),0)")
                ->selectRaw("pegawai.nama, departemen.nama as departemen, pegawai.photo, 
                	IFNULL((SELECT COUNT(a.id) FROM rekap_presensi a 
                	JOIN pegawai b ON a.id = b.id WHERE b.id = pegawai.id AND a.jam_datang LIKE '{$tanggal}' GROUP BY b.id),0) AS total,
                	IFNULL((SELECT COUNT(a.id) FROM rekap_presensi a 
                	JOIN pegawai b ON a.id = b.id WHERE b.id = pegawai.id AND a.jam_datang LIKE '{$tanggal}' AND a.status = 'Tepat Waktu' GROUP BY b.id),0) AS tepat_waktu")
                ->groupBy("rekap_presensi.id")
                ->orderByRaw('COUNT(rekap_presensi.id) DESC')
                ->limit(10)->get();
                // dd($data);
        $this->data = $data;
    }
}; ?>

<div>
    @if(count($data) > 0)
        @foreach($data as $item)
            <x-list-item :item="$item" value="nama" sub-value="departemen">
                <x-slot:avatar>
                    @php
                        $avatar = config('presensi.pegawai_url').$item->photo;
                    @endphp
                    <img src="{{ $avatar }}" alt="{{ $item->nama }}" class="w-24 hover:scale-150">
                </x-slot>
                <x-slot:actions>
                    <div class="flex flex-col justify-center gap-2 lg:gap-4">
                        <span class="text-sm truncate visible lg:invisible">{{ $item->nama }}</span>
                        <span class="text-sm text-gray-400 truncate visible lg:invisible">{{ $item->departemen }}</span>
                        <progress class="progress progress-success w-56" value="{{$item->tepat_waktu}}" max="{{$item->total}}"></progress>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Tepat Waktu / Masuk</span>
                            <span class="text-sm text-gray-600">{{ $item->tepat_waktu }} / {{ $item->total }}</span>
                        </div>
                    </div>
                </x-slot:actions>
            </x-list-item>
        @endforeach
    @endif
</div>
