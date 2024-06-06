<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use App\Models\JamJaga;
use App\Models\JadwalPegawai;
use App\Models\TemporaryPresensi;
use App\Models\SetKeterlambatan;
use App\Models\RekapPresensi;
use App\Models\GeolocationPresensi;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use WithFileUploads, Toast;
    public string $id_pegawai = '';
    public string $name = '';
    public $image;
    public $imageMasuk;
    public string $dep_id = '';
    public string $selectedJam = '';
    public bool $statusPresensi = false;
    public string $latitude = '';
    public string $longitude = '';

    public function mount()
    {
        $user = session('user');
        $this->id_pegawai = $user->id;
        $this->name = $user->nama;
        $this->dep_id = $user->cap;
        $this->statusPresensi = $this->cekPresensi();
        $this->dispatch('getLocation');
    }

    public function jamjaga()
    {
        $data =  JamJaga::query()
            ->where('dep_id', $this->dep_id)
            ->selectRaw("CONCAT(shift, ' ', jam_masuk, ' - ', jam_pulang) as name, shift as id")
            ->get();

        return $data;
    }

    public function cekPresensi() : bool
    {
        $cek = TemporaryPresensi::query()
                ->where('id', $this->id_pegawai)
                ->first();

        $rekap = RekapPresensi::query()
                ->where('id', $this->id_pegawai)
                ->where('jam_datang', 'like', '%'.date('Y-m-d').'%')
                ->first();

        if($cek){
            $this->imageMasuk = $cek->photo;
            return true;
        }else if($rekap){
            $this->imageMasuk = $rekap->photo;
            return true;
        }else{
            $this->imageMasuk = '';
            return false;
        }

        // $this->imageMasuk = $cek->photo ?? '';

        // return $cek ? true : false;
    }

    public function compressImage($image)
    {
        $manager = new ImageManager(array('driver' => 'gd'));
        $manager->make($image)->resize(300, 300)->save();
    }

    public function save()
    {
        $this->validate([
            'image' => 'required|image|mimes:jpeg,png|max:2048',
            'selectedJam' => 'required',
        ],[
            'image.required' => 'Foto tidak boleh kosong',
            'image.image' => 'File harus berupa gambar',
            'image.mimes' => 'File harus berformat jpeg atau png',
            'image.max' => 'Ukuran file maksimal 2MB',
            'selectedJam.required' => 'Shift tidak boleh kosong',
        ]);

        // dd($this->latitude, $this->longitude);
        if($this->latitude == '' || $this->longitude == ''){
            $this->error('Lokasi tidak ditemukan, pastikan izin lokasi diaktifkan', position: 'toast-bottom');
            return;
        }

        try{
            $imageName = time().'.'.$this->image->extension();
            $this->image->storeAs('public/presensi', $imageName);
            $url = env('APP_URL').'/storage/presensi/'.$imageName;

            DB::beginTransaction();
            $jam_jaga = JamJaga::query()
                ->join('pegawai', 'pegawai.departemen', '=', 'jam_jaga.dep_id')
                ->where('pegawai.id', $this->id_pegawai)
                ->where('jam_jaga.shift', $this->selectedJam)
                ->first();

            // dd($jam_jaga);

            $jadwal_pegawai = JadwalPegawai::query()
                ->where('id', $this->id_pegawai)
                ->where('h'.date('j'), $this->selectedJam)
                ->first();

            $set_keterlambatan = SetKeterlambatan::query()
                ->first();
            $toleransi = $set_keterlambatan->toleransi;
            $terlambat1 = $set_keterlambatan->terlambat1;
            $terlambat2 = $set_keterlambatan->terlambat2;

            $valid = RekapPresensi::query()
                ->where('id', $this->id_pegawai)
                ->where('shift', $this->selectedJam)
                ->where('jam_datang', 'like', '%'.date('Y-m-d').'%')
                ->first();

            if($valid){
                $this->error('Anda sudah melakukan presensi pada shift ini', position: 'toast-bottom');
                return;
            }

            $cek_presensi = $this->cekPresensi();

            if(!$cek_presensi){
                $status = 'Tepat Waktu';
                $keterlambatan = '';

                if((strtotime(date('Y-m-d H:i:s'))-strtotime(date('Y-m-d').' '.$jam_jaga->jam_masuk))>($toleransi*60)) {
                    $status = 'Terlambat Toleransi';
                }
                if((strtotime(date('Y-m-d H:i:s'))-strtotime(date('Y-m-d').' '.$jam_jaga->jam_masuk))>($terlambat1*60)) {
                    $status = 'Terlambat I';
                }
                if((strtotime(date('Y-m-d H:i:s'))-strtotime(date('Y-m-d').' '.$jam_jaga->jam_masuk))>($terlambat2*60)) {
                    $status = 'Terlambat II';
                }
                
                if((strtotime(date('Y-m-d H:i:s'))-strtotime(date('Y-m-d').' '.$jam_jaga->jam_masuk))>($toleransi*60)) {
                    $awal  = new \DateTime(date('Y-m-d').' '.$jam_jaga->jam_masuk);
                    $akhir = new \DateTime();
                    $diff = $akhir->diff($awal,true); // to make the difference to be always positive.
                    $keterlambatan = $diff->format('%H:%I:%S');
                }
                
                TemporaryPresensi::create([
                    'id' => $this->id_pegawai,
                    'shift' => $this->selectedJam,
                    'jam_datang' => date('Y-m-d H:i:s'),
                    'status' => $status,
                    'keterlambatan' => $keterlambatan ?? '',
                    'photo' => $url,
                ]);

            }else if($cek_presensi){

                $status = $cek->status;
                if((strtotime(date('Y-m-d H:i:s'))-strtotime(date('Y-m-d').' '.$jam_jaga->jam_pulang))<0) {
                    $status = $cek->status.' & PSW';
                }

                $awal  = new \DateTime($cek->jam_datang);
                $akhir = new \DateTime();
                $diff = $akhir->diff($awal,true); // to make the difference to be always positive.
                $durasi = $diff->format('%H:%I:%S');

                $cek_presensi->update([
                    'jam_pulang' => date('Y-m-d H:i:s'),
                    'status' => $status,
                    'durasi' => $durasi,
                ]);

                RekapPresensi::create([
                    'id' => $this->id_pegawai,
                    'shift' => $this->selectedJam,
                    'jam_datang' => $cek_presensi->jam_datang,
                    'jam_pulang' => date('Y-m-d H:i:s'),
                    'status' => $status,
                    'keterlambatan' => $cek_presensi->keterlambatan,
                    'durasi' => $cek_presensi->durasi,
                    'keterangan' => '-',
                    'photo' => $cek_presensi->photo,
                ]);

                $cek_presensi->delete();

            }

            DB::commit();
            $this->success('Presensi berhasil', position: 'toast-bottom');
            $this->cekPresensi();
            $this->dispatch('refresh');

        }catch(\Trowable $e){
            DB::rollBack();
            $this->error($e->getMessage());
        }
    }

    public function pulang()
    {
        if($this->latitude == '' || $this->longitude == ''){
            $this->error('Lokasi tidak ditemukan, pastikan izin lokasi diaktifkan', position: 'toast-bottom');
            return;
        }

        try{
            $rekap = RekapPresensi::query()
                ->where('id', $this->id_pegawai)
                ->where('jam_datang', 'like', '%'.date('Y-m-d').'%')
                ->first();
            $tmp = TemporaryPresensi::query()
                ->where('id', $this->id_pegawai)
                ->first();
            $jam_jaga = JamJaga::query()
                ->join('pegawai', 'pegawai.departemen', '=', 'jam_jaga.dep_id')
                ->where('pegawai.id', $this->id_pegawai)
                ->where('jam_jaga.shift', $tmp->shift)
                ->first();
            if($rekap){
                $this->error('Anda sudah melakukan presensi pulang', position: 'toast-bottom');
                return;
            }
            if(!$tmp){
                $this->error('Anda belum melakukan presensi masuk', position: 'toast-bottom');
                return;
            }else{
                DB::beginTransaction();
                $status = $tmp->status;
                if((strtotime(date('Y-m-d H:i:s'))-strtotime(date('Y-m-d').' '.$jam_jaga->jam_pulang))<0) {
                    $status = $tmp->status.' & PSW';
                }

                $awal  = new \DateTime($tmp->jam_datang);
                $akhir = new \DateTime();
                $diff = $akhir->diff($awal,true); // to make the difference to be always positive.
                $durasi = $diff->format('%H:%I:%S');

                RekapPresensi::create([
                    'id' => $this->id_pegawai,
                    'shift' => $tmp->shift,
                    'jam_datang' => $tmp->jam_datang,
                    'jam_pulang' => date('Y-m-d H:i:s'),
                    'status' => $status,
                    'keterlambatan' => $tmp->keterlambatan,
                    'durasi' => $durasi,
                    'keterangan' => '-',
                    'photo' => $tmp->photo,
                ]);

                $tmp->delete();
                DB::commit();
                $this->dispatch('refresh');
                $this->success('Presensi berhasil', position: 'toast-bottom');
            }
        }catch(\Throwable $e){
            DB::rollBack();
            // dd($e->getMessage());
            $this->error($e->getMessage());
        }
    }

    public function with(): array
    {
        return [
            'jamjaga' => $this->jamjaga(),
        ];
    }

}; ?>

<div>
    <x-header title="{{ $name }}" separator />
    <x-card>
        <div class="flex flex-col justify-center items-center space-y-2 ">
            @if($statusPresensi)
                <img src="{{ $imageMasuk }}" alt="" class="w-40 h-40">
                <div class="text-center">
                    <h1 class="text-2xl font-bold">Presensi Pulang</h1>
                    <p class="text-sm">Silahkan lakukan presensi pulang</p>
                </div>
                <x-button wire:click='pulang' wire:confirm='Anda yakin ingin melakukan presesnsi pulang sekarang ?' icon='o-camera' label="{{ $statusPresensi ? 'Pulang' : 'Masuk' }}" class="{{ $statusPresensi ? 'btn-error' : 'btn-primary' }} w-auto text-white" type="submit" spinner="pulang" />
            @else
            <x-form wire:submit="save">
            <x-file wire:model="image" accept="image/png, image/jpeg" change-text="{{ $statusPresensi ? 'Presensi Pulang' : 'Presensi Masuk' }}">
                <img src="{{ $imageMasuk ? $imageMasuk : (isset($image) ? $image->temporaryUrl() : asset('/images/camera.png')) }}" class="w-50 h-60"  />
            </x-file>
            <div class="w-auto">
                <x-select 
                    label="Pilih Shift" 
                    icon="o-clock" 
                    placeholder="Pilih Shift"
                    :options="$jamjaga"
                    wire:model="selectedJam" />
            </div>
            <x-button icon='o-camera' label="{{ $statusPresensi ? 'Pulang' : 'Masuk' }}" class="{{ $statusPresensi ? 'btn-error' : 'btn-primary' }} w-auto text-white" type="submit" spinner="save" />
            </x-form>
            @endif
        </div>
    </x-card>
    <livewire:home.riwayatpresensi />
</div>

@script
    <script>
       Livewire.on("getLocation", (event) => {
            // alert("Mendapatkan lokasi...");
            navigator.geolocation.getCurrentPosition(
            function (position) {
                const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;
                @this.set("latitude", latitude);
                @this.set("longitude", longitude);
            },
            function (error) {
                alert("Error izin lokasi tidak diberikan oleh user.");
            }
            );
        });
    </script>
@endscript
