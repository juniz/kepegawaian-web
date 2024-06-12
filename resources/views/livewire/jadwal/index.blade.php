<?php

use Livewire\Volt\Component;
use Illuminate\Support\Carbon;
use App\Models\JadwalPegawai;
use Illuminate\Support\Facades\DB;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    public $id_pegawai = '';
    public $tanggal;
    public $bulan = '';
    public $bln = '';
    public $tahun = '';
    public $jmlHari = 0;
    public $jadwal = [];
    public $shift = [];
    public $jam = "";
    public $departemen = '';
    public $selectHari = '';
    public bool $modal = false;

    public function mount()
    {
        $user = session('user');
        $this->id_pegawai = $user->id;
        $this->departemen = $user->cap;
        $this->tanggal = Carbon::now();
        $this->bulan = Carbon::now()->translatedFormat('F');
        $this->bln = Carbon::now()->translatedFormat('m');
        $this->tahun = Carbon::now()->translatedFormat('Y');
        $this->jmlHari = Carbon::now()->daysInMonth;
        $this->getJadwal();
        $this->jamJaga();
    }

    public function next()
    {
        $this->tanggal = $this->tanggal->addMonth();
        $this->bulan = $this->tanggal->translatedFormat('F');
        $this->bln = $this->tanggal->translatedFormat('m');
        $this->tahun = $this->tanggal->translatedFormat('Y');
        $this->jmlHari = $this->tanggal->daysInMonth;
        $this->getJadwal();
    }

    public function previous()
    {
        $this->tanggal = $this->tanggal->subMonth();
        $this->bulan = $this->tanggal->translatedFormat('F');
        $this->bln = $this->tanggal->translatedFormat('m');
        $this->tahun = $this->tanggal->translatedFormat('Y');
        $this->jmlHari = $this->tanggal->daysInMonth;
        $this->getJadwal();
    }

    public function getJadwal()
    {
        $this->jadwal = DB::table('jadwal_pegawai')
            ->where('id', $this->id_pegawai)
            ->where('tahun', $this->tahun)
            ->where('bulan', $this->bln)
            ->first();
        // dd($this->jadwal);
    }

    public function openDrawer($hari)
    {
        $shift = $this->getShift($hari);
        $this->selectHari = $hari;
        if($shift){
            $this->jam = $shift;
        }else{
            $this->jam = '';
        }
        $this->modal = true;
    }

    public function gantiJamJaga()
    {
        $shift = 'h' . $this->selectHari;
        try{
            $jadwal = DB::table('jadwal_pegawai')
                        ->upsert([
                            'id' => $this->id_pegawai,
                            'tahun' => $this->tahun,
                            'bulan' => $this->bln,
                            $shift => $this->jam
                        ], ['id', 'tahun', 'bulan'], [$shift]);
            $this->modal = false;
            $this->getJadwal();
        }catch(\Throwable $e){
            $this->error('Gagal menyimpan terjadi kesalahan');
        }
    }

    public function libur()
    {
        $shift = 'h' . $this->selectHari;
        try{
            $jadwal = DB::table('jadwal_pegawai')
                        ->where('id', $this->id_pegawai)
                        ->where('tahun', $this->tahun)
                        ->where('bulan', $this->bln)
                        ->update([
                            $shift => ''
                        ]);
            $this->modal = false;
            $this->getJadwal();
        }catch(\Throwable $e){
            $this->error('Gagal menyimpan terjadi kesalahan');
        }
    }

    public function jamJaga()
    {
        $this->shift = DB::table('jam_jaga')
                        ->where('dep_id', $this->departemen)
                        ->selectRaw("shift, CONCAT(shift, ' ', jam_masuk, ' - ', jam_pulang) as jam")
                        ->get();
    }

    public function getShift($hari)
    {
        $shift = 'h' . $hari;
        return $this->jadwal?->$shift;
    }

    public function cekHari($hari)
    {
        $tanggal = $this->tahun . '-' . $this->tanggal->translatedFormat('m') . '-' . $hari;
        return Carbon::parse($tanggal)->translatedFormat('l');
    }

    public function gantiJadwal()
    {
        dd('ganti jadwal');
    }
}; ?>

<div>
    <x-header title="Jadwal Pegawai" separator />
    <x-card>
        <div class="grid grid-cols-2 md:grid-cols-7 gap-2">
            <div class="col-span-3 md:col-span-7">
                <div class="flex items-center justify-between mb-4">
                    <button wire:click='previous()' class="px-4 py-2 text-sm font-medium text-gray-500 bg-white rounded-md shadow-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Previous</button>
                    <h2 class="text-lg font-medium text-gray-700">{{ $bulan }} {{ $tahun }}</h2>
                    <button wire:click='next' class="px-4 py-2 text-sm font-medium bg-black text-white rounded-md shadow-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Next</button>
                </div>
            </div>
            <div class="col-span-2 md:col-span-7">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-2">
                    @for ($i = 1; $i <= $jmlHari; $i++)
                        @if(empty($this->getShift($i)))
                            <div wire:click='openDrawer({{$i}})' class="flex flex-col items-center justify-center h-20 bg-red-500 rounded-md shadow-md hover:bg-red-300">
                                <span class="text-xs font-bold text-white">{{ $this->cekHari($i) }}</span>
                                <span class="text-base font-medium text-white">{{ $i }}</span>
                                <span class="text-xs font-normal text-white">{{ $this->getShift($i) }}</span>
                            </div>
                        @else
                            <div wire:click='openDrawer({{$i}})' class="flex flex-col items-center justify-center h-20 bg-white rounded-md shadow-md hover:bg-gray-100">
                                <span class="text-xs font-bold text-gray-700">{{ $this->cekHari($i) }}</span>
                                <span class="text-base font-medium text-gray-700">{{ $i }}</span>
                                <span class="text-xs font-normal text-gray-700">{{ $this->getShift($i) }}</span>
                            </div>
                        @endif
                    @endfor
                </div>
            </div>
            <x-drawer 
                wire:model="modal" 
                class="w-11/12 lg:w-1/3" 
                title="Ganti Jadwal"
                separator
                with-close-button
                close-on-escape
                right
            >
                <div class="mb-5">
                    <x-choices-offline
                        label="Jam Jaga"
                        wire:model="jam"
                        :options="$shift"
                        option-label='jam'
                        option-value='shift'
                        icon="o-clock"
                        height="max-h-96" {{-- Default is `max-h-64`  --}}
                        single
                        searchable
                    />
                </div>
                <x-slot:actions>
                    <x-button label="Batal" @click="$wire.modal = false" />
                    <x-button label="Libur" wire:click='libur' class="btn-error" />
                    <x-button label="Simpan" wire:click='gantiJamJaga' class="btn-primary" icon="o-check" />
                </x-slot:actions>
            </x-modal>
        </div>
    </x-card>
</div>

