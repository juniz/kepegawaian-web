<?php

use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use Toast;
    public $selectedtab = 'form-rapat';
    public $id_user = '';
    public $rapat = '';
    public $nama = '';
    public $instansi = '';
    public $tanda_tangan = '';

    public function mount()
    {
        $user = session('user');
        if($user){
            $this->id_user = $user->id;
            $this->nama = $user->nama;
            $nrp = '';
            if(str_contains($user->username, 'TKK')){
                $nrp = 'TKK';
            }else{
                $nrp = $user->username;
            }
            $this->instansi = $nrp.' / '.$user->cap;
        }
        
    }

    // public function openDrawer()
    // {
    //     $this->dispatch('openDrawer');
    // }

    public function simpan()
    {
        $this->validate([
            'rapat' => 'required',
            'nama' => 'required',
            'instansi' => 'required',
            'tanda_tangan' => 'required'
        ],[
            'rapat.required' => 'Rapat Harus Diisi',
            'nama.required' => 'Nama Harus Diisi',
            'instansi.required' => 'Instansi Harus Diisi',
            'tanda_tangan.required' => 'Tanda Tangan Harus Diisi'
        ]);

        try{
            DB::table('rapat')->insert([
                'tanggal' => date('Y-m-d'),
                'rapat' => $this->rapat,
                'nama' => $this->nama,
                'instansi' => $this->instansi,
                'tanda_tangan' => $this->tanda_tangan,
            ]);
            $this->success('Data Berhasil Disimpan');
            $this->resetExcept('id_user');
            $this->dispatch('refresh');
        }catch(\Throwable $e){
            $this->error($e->getMessage());
        }
    }
}; ?>

<div>
    <x-header title="Form Rapat" separator >
    </x-header>
    <x-card>
        <x-tabs wire:model="selectedtab">
            <x-tab name='form-rapat' label='Rapat' icon='o-users'>
                <x-form wire:submit='simpan'>
                    <x-input label="Rapat" wire:model="rapat" icon="c-user-group" />
                    <x-input label="Nama" wire:model="nama" icon="c-user" />
                    <x-input label="NRP / Instansi / Jabatan" wire:model="instansi" icon="c-building-office" />
                    <x-signature 
                        wire:model="tanda_tangan"
                        clear-text="Hapus"
                        height="400"
                    />
                    <x-button label="Simpan" class="btn-primary" type="submit" spinner="simpan" />
                </x-form>
            </x-tab>
            <x-tab name='data-rapat' label='Riwayat' icon='o-table-cells'>
                <livewire:rapat.rekap />
            </x-tab>
        </x-tabs>
    </x-card>
</div>

@section('head')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.2.0/dist/signature_pad.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
    <script>
        flatpickr.localize(flatpickr.l10ns.id);
    </script>
@endsection
