<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On; 
use App\Models\PengajuanCuti;

new class extends Component {
    use WithPagination;
    public string $nik = '';

    public function mount($nik)
    {
        $this->nik = $nik;
    }

    #[On('refresh')]
    public function getRiwayatCuti()
    {
        return PengajuanCuti::where('nik', $this->nik)->orderBy('tanggal', 'desc')->paginate(10);
    }

    public function headers()
    {
        return [
            ['key' => 'no_pengajuan', 'label' => 'No'],
            ['key' => 'tanggal', 'label' => 'Tanggal'],
            ['key' => 'tanggal_awal', 'label' => 'Tanggal Awal'],
            ['key' => 'tanggal_akhir', 'label' => 'Tanggal Akhir'],
            ['key' => 'jumlah', 'label' => 'Jumlah'],
            ['key' => 'urgensi', 'label' => 'Urgensi'],
            ['key' => 'alamat', 'label' => 'Alamat'],
            ['key' => 'kepentingan', 'label' => 'Kepentingan'],
            ['key' => 'status', 'label' => 'Status']
        ];
    }

    public function with()
    {
        return [
            'riwayatCuti' => $this->getRiwayatCuti(),
            'headers' => $this->headers()
        ];
    }

}; ?>

<div>
    <x-table :headers="$headers" :rows="$riwayatCuti" with-pagination />
</div>
