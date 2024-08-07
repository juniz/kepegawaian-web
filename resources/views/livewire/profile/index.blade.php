<?php

use Livewire\Volt\Component;
use App\Models\Pegawai;
use App\Models\Departemen;

new class extends Component {
    public $user;
    public $photo;
    public $nip;
    public $departemen;

    public function mount()
    {
        $this->user = session('user');
        $this->photo = config('presensi.pegawai_url').$this->user->photo;
        $this->nip = $this->getNip();
        $this->departemen = $this->getDepartemen();
    }

    public function getNip()
    {
        return Pegawai::where('id', $this->user->id)->first()->nik;
    }

    public function getDepartemen()
    {
        return Departemen::where('dep_id', $this->user->cap)->first()->nama;
    }
}; ?>

<div>
    <x-header title="Profile" separator />
    <x-card>
        <div class="flex flex-col gap-4">
            <div class="flex flex-col justify-center items-center gap-2 my-4">
                <div class="avatar">
                    <div class="ring-primary ring-offset-base-100 w-24 rounded-full ring ring-offset-2">
                        <img src="{{ $photo }}" alt="{{ $user->nama }}" class="rounded w-36 h-36">
                    </div>
                </div>
                <span class="font-bold overflow-hidden">{{ $user->nama }}</span>
                <span class="font-light text-xs">{{ $nip }}</span>
            </div>
            <div class="flex flex-col items-center gap-2">
                <div class="flex flex-col items-center gap-1">
                    <span class="font-bold">Tempat, Tanggal Lahir</span>
                    <span class="font-light text-xs">{{ $user->tmp_lahir }}, {{ $user->tgl_lahir }}</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                    <span class="font-bold">Alamat</span>
                    <span class="font-light text-xs">{{ $user->alamat }}</span>
                </div>
                <div class="flex flex-col items-center gap-1 mb-4">
                    <span class="font-bold">Departemen</span>
                    <span class="font-light text-xs">{{ $departemen }}</span>
                </div>
                <x-button icon-right="o-power" class="btn-error btn-sm" tooltip="Keluar Aplikasi" link="/logout"/>
            </div>
        </div>
    </x-card>
</div>
