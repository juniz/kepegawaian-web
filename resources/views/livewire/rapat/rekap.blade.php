<?php

use Livewire\Volt\Component;
use App\Models\PengajuanIzin;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB; 
use Carbon\Carbon;

new class extends Component {
    use WithPagination;
    public $tanggal;
    public $cari = '';
    public $showDrawer = false;

    public function mount()
    {
        $this->tanggal = date('Y-m-d');
    }

    #[On('refresh')]
    public function getRekap()
    {
        return DB::table('rapat')->where('tanggal', $this->tanggal)->where('rapat', 'like', '%'.$this->cari.'%')->get();
    }

    #[On('openDrawer')]
    public function openDrawer()
    {
        $this->showDrawer = true;
        // dd($this->showDrawer);
    }

    public function print()
    {
        $tanggal = Carbon::parse($this->tanggal)->format('Y-m-d');
        // dd($tanggal);
        return redirect()->to('/rapat/print?tanggal='.$tanggal.'&cari='.$this->cari);
    }

    public function headers()
    {
        return [
            ['key' => 'tanggal', 'label' => 'Tanggal'],
            ['key' => 'rapat', 'label' => 'Rapat'],
            ['key' => 'nama', 'label' => 'Nama'],
            ['key' => 'instansi', 'label' => 'Instansi'],
            ['key' => 'tanda_tangan', 'label' => 'Tanda Tangan']
        ];
    }

    public function with()
    {
        return [
            'riwayatPresensi' => $this->getRekap(),
            'headers' => $this->headers(),
            'configDatepicker' => [
                'format' => 'Y-m-d',
            ]
        ];
    }

}; ?>

<div>
    <div class="flex flex-row justify-end gap-2 mb-4">
        <x-button icon="o-printer" class="btn-sm" label='Cetak' wire:click='print' />
        <x-button icon="s-magnifying-glass" class="btn-sm" label='Pencarian' @click.stop="$dispatch('openDrawer')"  />
    </div>
    <x-table :headers="$headers" :rows="$riwayatPresensi" striped >
        @scope('cell_tanda_tangan', $presensi)
            <img src="{{$presensi->tanda_tangan}}" alt="tanda_tangan" class="w-20">
        @endscope
    </x-table>
    <x-drawer 
        wire:model="showDrawer" 
        class="w-11/12 lg:w-1/3" 
        title="Pencarian Rapat"
        separator
        with-close-button
        close-on-escape 
        right
    >
        <div class="flex flex-col gap-4">
            <x-input icon="s-magnifying-glass" placeholder="Cari..." label='Cari Nama Rapat' wire:model.live='cari' />
            <x-datepicker label="Tanggal Rapat" wire:model.live="tanggal" icon="o-calendar" :config="$configDatepicker" />
        </div>
        <x-slot:actions>
            <x-button label="Tutup" @click="$wire.showDrawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>
