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
use Mary\Traits\Toast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use App\Http\Traits\Telegram;

new class extends Component {
    use WithFileUploads, Toast, Telegram;
    public $selectedTab = 'presensi';
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
            $this->statusPresensi = true;
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

    public function simpanLokasi()
    {
        DB::table('geolocation_presensi')
            ->insert([
                'id' => $this->id_pegawai,
                'tanggal' => date('Y-m-d'),
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ]);
    } 

    public function save()
    {
        $this->validate([
            'image' => 'required',
            'selectedJam' => 'required',
        ],[
            'image.required' => 'Foto tidak boleh kosong',
            'selectedJam.required' => 'Shift tidak boleh kosong',
        ]);

        // dd($this->latitude, $this->longitude);
        if($this->latitude == '' || $this->longitude == ''){
            $this->error('Lokasi tidak ditemukan, pastikan izin lokasi diaktifkan', position: 'toast-bottom');
            return;
        }

        try{
            // $img = $this->image;
            // $image_parts = explode(";base64,", $img);
            // $image_type_aux = explode("image/", $image_parts[0]);
            // $image_type = $image_type_aux[1];
            
            // $image_base64 = base64_decode($image_parts[1]);
            // $fileName = uniqid() . '.png';

            // $url = env('APP_URL') . '/storage/presensi/' . $fileName;
            // Storage::disk('public')->put('presensi/'.$fileName, $image_base64);
            // $manager = new ImageManager(new Driver());

            $imageName = time().'-'.$this->id_pegawai.'.'.$this->image->extension();
            // $image = $manager->read($this->image->getRealPath());
            // $image->encode('jpg', 65);
            // $image->save(storage_path('app/public/presensi/'.$imageName));
            // $img = $manager->make($this->image->getRealPath())->encode('jpg', 65)->fit(760, null, function ($c) {
            //     $c->aspectRatio();
            //     $c->upsize();
            // });
            ImageOptimizer::optimize($this->image->getRealPath());
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

                if(config('presensi.jarak') != 0){
                    $jarak = $this->hitungJarak($this->latitude, $this->longitude, config('presensi.latitute'), config('presensi.longitude'));

                    if($jarak > config('presensi.jarak')){
                        $this->error('Anda diluar jangkauan lokasi');
                        return;
                    }
                }

                $this->simpanLokasi();

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
            $this->sendMessage('Presensi RSB Nganjuk masuk gagal, '.$e->getMessage());
            $this->error($e->getMessage());
        }
    }

    public function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $theta = (float)$lon1 - (float)$lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        return $meters;
    }

    public function pulang()
    {
        if($this->latitude == '' || $this->longitude == ''){
            $this->error('Lokasi tidak ditemukan, pastikan izin lokasi diaktifkan');
            return;
        }

        try{
            $rekap = RekapPresensi::query()
                ->where('id', $this->id_pegawai)
                ->where('jam_datang', 'like', '%'.date('Y-m-d').'%')
                ->first();

            if($rekap){
                $this->error('Anda sudah melakukan presensi pulang');
                return;
            }

            $tmp = TemporaryPresensi::query()
                ->where('id', $this->id_pegawai)
                ->first();
            
            if(!$tmp){
                $this->error('Anda belum melakukan presensi masuk');
                return;
            }else{

                $jam_jaga = JamJaga::query()
                    ->join('pegawai', 'pegawai.departemen', '=', 'jam_jaga.dep_id')
                    ->where('pegawai.id', $this->id_pegawai)
                    ->where('jam_jaga.shift', $tmp->shift)
                    ->first();

                if(date('H:i:s') < $jam_jaga->jam_pulang){
                    $this->error('Belum waktunya pulang');
                    return;
                }

                $status = $tmp->status;
                if((strtotime(date('Y-m-d H:i:s'))-strtotime(date('Y-m-d').' '.$jam_jaga->jam_pulang))<0) {
                    $status = $tmp->status.' & PSW';
                }

                $awal  = new \DateTime($tmp->jam_datang);
                $akhir = new \DateTime();
                $diff = $akhir->diff($awal,true); // to make the difference to be always positive.
                $durasi = $diff->format('%H:%I:%S');

                DB::beginTransaction();
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
                
            }

            DB::commit();
            $this->success('Presensi berhasil');
            $this->cekPresensi();
            $this->dispatch('refresh');

        }catch(\Throwable $e){
            DB::rollBack();
            // dd($e->getMessage());
            $this->sendMessage('Presensi RSB Nganjuk Pulang gagal, '.$e->getMessage());
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
        <x-tabs wire:model="selectedTab">
            <x-tab name="presensi" label="Presensi" icon="o-users" >
                <div class="flex flex-col justify-center items-center space-y-2 ">
                    {{-- <div id="root">
                        <p>Upload an image and see the result</p>
                        <input id="img-input" type="file" accept="image/*" style="display:block" />
                    </div> --}}
                    @if($statusPresensi)
                        <img src="{{ $imageMasuk }}" alt="" class="w-40 h-48 rounded-box">
                        <div class="text-center">
                            <h1 class="text-2xl font-bold">Presensi Pulang</h1>
                            <p class="text-sm">Silahkan lakukan presensi pulang</p>
                        </div>
                        <x-button wire:click='pulang' wire:confirm='Anda yakin ingin melakukan presensi pulang sekarang ?' icon='o-camera' label="{{ $statusPresensi ? 'Pulang' : 'Masuk' }}" class="{{ $statusPresensi ? 'btn-error' : 'btn-primary' }} w-auto text-white" type="submit" spinner="pulang" />
                    @else
                    <x-form 
                        wire:submit="save"
                        x-data="{ uploading: false, progress: 0 }"
                        x-on:livewire-upload-start="uploading = true"
                        x-on:livewire-upload-finish="uploading = false"
                        x-on:livewire-upload-cancel="uploading = false"
                        x-on:livewire-upload-error="uploading = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                    >
                    <x-file id="img-input" accept="image/png, image/jpeg" class="my-image-field" change-text="Ganti" capture>
                        <img src="{{ $imageMasuk ? $imageMasuk : (isset($image) ? $image->temporaryUrl() : asset('/images/camera.png')) }}" class="w-50 h-60 rounded-box bg-contain"  />
                    </x-file>
                    <span class="text-sm text-center text-red-600">Harap gunakan kamera depan</span>
                    <div x-show="uploading">
                        <progress class="progress progress-primary w-56" x-bind:value="progress" max="100"></progress>
                    </div>
                    <div class="w-auto">
                        <x-select 
                            label="Pilih Shift" 
                            icon="o-clock" 
                            placeholder="Pilih Shift"
                            :options="$jamjaga"
                            wire:model="selectedJam" />
                    </div>
                    <template x-if="uploading">
                        <x-button icon='o-camera' label="{{ $statusPresensi ? 'Pulang' : 'Masuk' }}" class="{{ $statusPresensi ? 'btn-error' : 'btn-primary' }} w-auto text-white btn-submit" type="submit" spinner="save" disabled/>
                    </template>
                    <template x-if="!uploading">
                        <x-button icon='o-camera' label="{{ $statusPresensi ? 'Pulang' : 'Masuk' }}" class="{{ $statusPresensi ? 'btn-error' : 'btn-primary' }} w-auto text-white btn-submit" type="submit" spinner="save"/>
                    </template>
                    </x-form>
                    @endif
                </div>
            </x-tab>
            <x-tab name="riwayat" label="Riwayat Presensi" icon="o-table-cells" >
                <livewire:home.riwayatpresensi />
            </x-tab>
        </x-tabs>
    </x-card>
</div>

@section('head')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.2.0/dist/signature_pad.umd.min.js"></script>
@endsection

@script
<script>
    let watchID = navigator.geolocation.getCurrentPosition(
        function success(pos) {
            var loc = pos.coords;
            latitude = loc.latitude;
            longitude = loc.longitude;
            @this.set('latitude', latitude);
            @this.set('longitude', longitude);
            navigator.geolocation.clearWatch(watchID);
        }, 
        function error(err) {
            alert('ERROR(' + err.code + '): ' + err.message);
        }, 
        {
            maximumAge:0, 
            timeout:5000, 
            enableHighAccuracy:false
        }
    );

    const input = document.querySelector('.my-image-field');

    const compressImage = async (file, { quality = 1, type = file.type }) => {
        // Get as image data
        const imageBitmap = await createImageBitmap(file);

        // Draw to canvas
        const canvas = document.createElement('canvas');
        canvas.width = imageBitmap.width;
        canvas.height = imageBitmap.height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(imageBitmap, 0, 0);

        // Turn into Blob
        return await new Promise((resolve) =>
            canvas.toBlob(resolve, type, quality)
        );
    };

    input.addEventListener('change', async (e) => {
        
        const compressedFile = await compressImage(e.target.files[0], {
                quality: 0.5,
                type: 'image/jpeg',
            });
        console.log(compressedFile);
        $wire.upload('image', compressedFile, (file) => {
            console.log(file);
        });
    });

</script>
@endscript

