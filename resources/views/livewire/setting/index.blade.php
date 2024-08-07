<?php

use Livewire\Volt\Component;
use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    public $selectedPegawai = '';
    public $akses = [];

    public function mount()
    {
        $this->akses = $this->getAksesPresensi();
    }

    public function getPegawai()
    {
        return Pegawai::where('stts_aktif', "AKTIF")->get();
    }

    public function getAksesPresensi()
    {
        return DB::table('kepegawaian_akses')
                ->join('pegawai', 'kepegawaian_akses.id_pegawai', '=', 'pegawai.id')
                ->select('kepegawaian_akses.id', 'pegawai.nama', 'kepegawaian_akses.menu')
                ->get();
    }

    public function headers()
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'hidden'],
            ['key' => 'nama', 'label' => 'Nama'],
            ['key' => 'menu', 'label' => 'Menu']
        ];
    }

    public function tambah()
    {
        // dd($this->selectedPegawai);
        $this->validate([
            'selectedPegawai' => 'required'
        ],[
            'selectedPegawai.required' => 'Pilih Pegawai'
        ]);

        try{

            DB::table('kepegawaian_akses')->insert([
                'id_pegawai' => $this->selectedPegawai,
                'menu' => 'presensi'
            ]);
            $this->success('Data Berhasil Disimpan');
            $this->akses = $this->getAksesPresensi();

        }catch(\Exception $e){
            dd($e->getMessage());
            $this->error($e->getMessage());
        }
    }

    public function hapus($id)
    {
        try{
            DB::table('kepegawaian_akses')->where('id', $id)->delete();
            $this->success('Data Berhasil Dihapus');
            $this->akses = $this->getAksesPresensi();
        }catch(\Exception $e){
            $this->error($e->getMessage());
        }
    }

    public function with()
    {
        return [
            'pegawai' => $this->getPegawai(),
            // 'akses' => $this->getAksesPresensi(),
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <x-header title="Setting" separator />
    <div class="flex flex-col gap-4">
        <x-card>
            <x-form wire:submit='tambah'>
                <x-choices-offline
                    label='Pilih Pegawai'
                    wire:model='selectedPegawai'
                    :options='$pegawai'
                    option-label='nama'
                    option-value='id'
                    icon='o-users'
                    single
                    searchable
                >
                <x-slot:append>
                    {{-- Add `rounded-e-none` (RTL support) --}}
                    <x-button label="Tambah" type='submit' icon="o-plus" class="rounded-s-none btn-primary" />
                </x-slot:append>
                </x-choices-offline>
            </x-form>
        </x-card>
        <x-card title="Data Hak Akses">
            <x-table :headers="$headers" :rows="$akses" striped >
                @scope('actions', $akses)
                    <x-button icon="o-trash" wire:confirm='Apakah anda yakin ingin menghapus data ini?' wire:click="hapus({{ $akses->id }})" spinner class="btn-sm btn-error" />
                @endscope
            </x-table>
        </x-card>
    </div>
</div>
