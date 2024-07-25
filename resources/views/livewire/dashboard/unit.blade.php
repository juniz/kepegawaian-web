<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

new class extends Component {

    public $data = [];

    #[On('getUnit')]
    public function getData($tanggal, $unit)
    {
        $tgl = $tanggal.'%';
        $data = DB::table('pegawai')
                ->where('pegawai.departemen', $unit)
                ->where('pegawai.stts_aktif', 'AKTIF')
                ->selectRaw("pegawai.nama, pegawai.photo,
                    IFNULL((SELECT COUNT(a.id) FROM rekap_presensi a 
                    WHERE a.id = pegawai.id AND a.jam_datang LIKE '{$tgl}' GROUP BY a.id), 0) AS total, 
                    IFNULL((SELECT COUNT(a.id) FROM rekap_presensi a 
                    WHERE a.id = pegawai.id AND a.jam_datang LIKE '{$tgl}' AND (a.status = 'Tepat Waktu' OR a.status = 'Tepat Waktu & PSW') GROUP BY a.id), 0) AS tepat_waktu, 
                    IFNULL((SELECT COUNT(a.id) FROM rekap_presensi a 
                    WHERE a.id = pegawai.id AND a.jam_datang LIKE '{$tgl}' AND (a.status = 'Terlambat Toleransi' OR a.status = 'Terlambat Toleransi & PSW') GROUP BY a.id), 0) AS toleransi,
                    IFNULL((SELECT COUNT(a.id) FROM rekap_presensi a 
                    WHERE a.id = pegawai.id AND a.jam_datang LIKE '{$tgl}' AND (a.status = 'Terlambat I' OR a.status = 'Terlambat I & PSW') GROUP BY a.id), 0) AS terlambat1,
                    IFNULL((SELECT COUNT(a.id) FROM rekap_presensi a 
                    WHERE a.id = pegawai.id AND a.jam_datang LIKE '{$tgl}' AND (a.status = 'Terlambat II' OR a.status = 'Terlambat II & PSW') GROUP BY a.id), 0) AS terlambat2")
        		->get();
        // dd($data);
        $this->data = $data;
    }
    
}; ?>

<div>
    @if(count($data) > 0)
        @foreach($data as $item)
            <x-list-item :item="$item" value="nama">
                <x-slot:avatar>
                    @php
                        $avatar = config('presensi.pegawai_url').$item->photo;
                    @endphp
                    <img src="{{ $avatar }}" alt="{{ $item->nama }}" class="w-24 hover:scale-150">
                </x-slot>
                <x-slot:actions>
                    <div class="flex flex-col justify-center gap-2 lg:gap-4">
                        <span class="text-sm truncate visible lg:invisible">{{ $item->nama }}</span>
                        <div class="flex gap-2">
                            <div class="grow">
                                <progress class="progress progress-success w-48 lg:w-56" value="{{$item->tepat_waktu}}" max="{{$item->total}}"></progress>
                            </div>
                            <div class="flex-none">
                                <span class="text-sm text-gray-600">{{ $item->tepat_waktu }}</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <div class="grow">
                                <progress class="progress progress-warning w-48 lg:w-56" value="{{$item->toleransi}}" max="{{$item->total}}"></progress>
                            </div>
                            <div class="flex-none">
                                <span class="text-sm text-gray-600">{{ $item->toleransi }}</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <div class="grow">
                                <progress class="progress progress-error w-48 lg:w-56" value="{{$item->terlambat1}}" max="{{$item->total}}"></progress>
                            </div>
                            <div class="flex-none">
                                <span class="text-sm text-gray-600">{{ $item->terlambat1 }}</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <div class="grow">
                                <progress class="progress progress-error w-48 lg:w-56" value="{{$item->terlambat2}}" max="{{$item->total}}"></progress>
                            </div>
                            <div class="flex-none">
                                <span class="text-sm text-gray-600">{{ $item->terlambat2 }}</span>
                            </div>
                        </div>
                        {{-- <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Terlambat I</span>
                            <span class="text-sm text-gray-600">{{ $item->terlambat1 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Terlambat II</span>
                            <span class="text-sm text-gray-600">{{ $item->terlambat2 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Terlambat / Masuk</span>
                            <span class="text-sm text-gray-600">{{ $item->total_terlambat }} / {{ $item->total }}</span>
                        </div> --}}
                    </div>
                </x-slot:actions>
            </x-list-item>
        @endforeach
    @endif
</div>
