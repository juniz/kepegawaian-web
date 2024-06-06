<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Mary\Traits\Toast;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;

new class extends Component {
    use Toast;
    public $selectedTab = 'form-cuti';
    public $id_user = '';
    public $tgl_awal = '';
    public $tgl_akhir = '';
    public $nik = '';
    public $urgensi = '';
    public $nik_pj = '';
    public $kepentingan = '';
    public $alamat = '';
    public $status = '';
    public $tgl_pengajuan = '';

    public function mount()
    {
        $user = session('user');
        $this->id_user = $user->id;
        $this->tgl_awal = date('Y-m-d');
        $this->tgl_akhir = date('Y-m-d');
        $this->tgl_pengajuan = date('Y-m-d');
        $this->urgensi = 'Tahunan';
        $this->getNik();
    }

    public function getNik()
    {
        $this->nik = Pegawai::where('id', $this->id_user)->first()->nik;
    }

    public function pegawai()
    {
        return Pegawai::where('stts_aktif', "AKTIF")->get();
    }

    public function simpan()
    {
        $this->validate([
            'nik_pj' => 'required',
            'tgl_awal' => 'required',
            'tgl_akhir' => 'required',
            'urgensi' => 'required',
            'alamat' => 'required',
            'kepentingan' => 'required'
        ],[
            'nik_pj.required' => 'Pilih PJ',
            'tgl_awal.required' => 'Tanggal Mulai Izin Harus Diisi',
            'tgl_akhir.required' => 'Tanggal Selesai Izin Harus Diisi',
            'urgensi.required' => 'Pilih Jenis Izin',
            'alamat.required' => 'Alamat Harus Diisi',
            'kepentingan.required' => 'Kepentingan / Alasan Izin Harus Diisi'
        ]);

        try{

            $noCuti = DB::table('pengajuan_cuti')->where('tanggal', $this->tgl_pengajuan)->max('no_pengajuan');
            $last = substr($noCuti ?? 0 , -3) + 1;
            $lastNo = 'PC'.date('Ymd').sprintf("%03d", $last);
            $dtFormat1 = new DateTime($this->tgl_awal);
            $dtFormat2 = new DateTime($this->tgl_akhir);
            $dateDiff = $dtFormat1->diff($dtFormat2);
            $jumlah =$dateDiff->format('%d');
            $jml = $jumlah + 1;

            $data = [
                'no_pengajuan' => $lastNo,
                'jumlah' => $jml,
                'nik' => $this->nik,
                'nik_pj' => $this->nik_pj,
                'tanggal_awal' => $this->tgl_awal,
                'tanggal_akhir' => $this->tgl_akhir,
                'urgensi' => $this->urgensi,
                'kepentingan' => $this->kepentingan,
                'alamat' => $this->alamat,
                'status' => 'Proses Pengajuan',
                'tanggal' => $this->tgl_pengajuan
            ];

            PengajuanCuti::create($data);
            $this->dispatch('refresh');
            $this->reset();
            $this->success('Data Berhasil Disimpan');

        }catch(\Throwable $e){
            // dd($e->getMessage());
            $this->error('Gagal menyimpan terjadi kesalahan');
        }
    }

    public function jnsCuti()
    {
        return [
            [
                'id' => 'Tahunan',
                'name' => 'Tahunan'    
            ],
            [
                'id' => 'Sakit',
                'name' => 'Sakit'
            ],
            [
                'id' => 'Istimewa',
                'name' => 'Istimewa'
            ],
            [
                'id' => 'Ibadah Keagamaan',
                'name' => 'Ibadah Keagamaan'
            ],
            [
                'id' => 'Karena Alasan Penting',
                'name' => 'Karena Alasan Penting'
            ],
            [
                'id' => 'Di luar tanggungan negara',
                'name' => 'Di luar tanggungan negara'
            ],
            [
                'id' => 'Tahunan ke luar negeri',
                'name' => 'Tahunan ke luar negeri'
            ],
            [
                'id' => 'Keterangan lainnya',
                'name' => 'Keterangan lainnya'
            ]
        ];
    }

    public function with()
    {
        return [
            'pegawai' => $this->pegawai(),
            'jnsCuti' => $this->jnsCuti()
        ];
    }

}; ?>

<div>
    <x-header title="Form Pengajuan Cuti" separator />
    <x-card>
        <x-tabs wire:model="selectedTab">
            <x-tab name="form-cuti" label="Input" icon="o-users">
                <x-form wire:submit='simpan'>
                    <x-choices-offline
                        label='Pilih PJ'
                        wire:model='nik_pj'
                        :options='$pegawai'
                        option-label='nama'
                        option-value='nik'
                        icon='o-users'
                        single
                        searchable
                    />
                    <x-datetime label="Tanggal Mulai Cuti" wire:model="tgl_awal" icon="o-calendar" />
                    <x-datetime label="Tanggal Selesai Cuti" wire:model="tgl_akhir" icon="o-calendar" />
                    <x-select label="Jenis Cuti" :options="$jnsCuti" wire:model="urgensi" icon="o-folder" />
                    <x-input label="Alamat" wire:model="alamat" icon="c-building-office" />
                    <x-input label="Kepentingan / Alasan Cuti" wire:model="kepentingan" icon="c-building-office" />
                    <x-button label="Simpan" class="btn-primary" type="submit" spinner="simpan" />
                </x-form>
            </x-tab>
            <x-tab name="data-izin" label="Riwayat" icon="o-table-cells">
                <livewire:cuti.riwayat :nik='$nik' />
            </x-tab>
        </x-tabs>
    </x-card>
</div>


