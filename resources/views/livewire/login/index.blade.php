<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Mary\Traits\Toast;
use Livewire\Attributes\{Layout};

new 
#[Layout('components.layouts.login')]
class extends Component {
    use Toast;
    public string $username = '';
    public string $password = '';

    public function save()
    {
        $this->validate([
            'username' => 'required',
            'password' => 'required',
        ],[
            'username.required' => 'Username Harus Diisi',
            'password.required' => 'Password Harus Diisi',
        
        ]);

        try{
            // dd($this->username, $this->password);
            $cek = DB::table('user')
                    ->join('pegawai', 'pegawai.nik', '=', DB::Raw("AES_DECRYPT(user.id_user,'nur')"))
                    ->whereRaw('user.id_user = AES_ENCRYPT(?,"nur")',[$this->username])
                    ->where('pegawai.stts_aktif', 'AKTIF')
                    ->selectRaw("pegawai.id, AES_DECRYPT(user.id_user,'nur') as username, AES_DECRYPT(user.password,'windi') as password, pegawai.nama, pegawai.departemen as cap, pegawai.tmp_lahir, pegawai.tgl_lahir, pegawai.alamat, pegawai.photo")
                    ->first();
            // dd($cek);
            if($cek){
                if($cek->password == $this->password){
                    session(['user' => $cek]);
                    $this->redirect('/dashboard');
                }else{
                    $this->error('Password Salah');
                }
            }else{
                $this->error('Username Tidak Ditemukan');
            }

        }catch(\Trowable $e){
            $this->error($e->getMessage());
        }
    }
}; ?>

<div>
    <div class="min-h-screen flex flex-col items-center justify-center py-6 px-4">
        <div class="mb-8 felx flex-col space-y-4">
            <img src="{{ asset('images/logo.png') }}" alt="logo" class="w-32 h-32 mx-auto">
            <h1 class="text-2xl text-center font-bold text-gray-800 dark:text-white">SDM Handal</h1>
        </div>
        <div class="max-w-md w-full border py-8 px-6 rounded-lg border-gray-300 bg-white dark:bg-slate-800">
            <x-form wire:submit="save" class="space-y-3">
                <x-input label="Username" wire:model="username" inline />
                <x-input label="Password" wire:model="password" type="password" inline />             
                <x-button label="Login" class="btn-primary" type="submit" spinner="save" />
            </x-form>
        </div>
    </div>
</div>