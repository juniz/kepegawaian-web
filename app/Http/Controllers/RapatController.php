<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RapatController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->tanggal;
        $cari = $request->cari;
        $rapat = DB::table('rapat')
            ->where('tanggal', 'like', "%$tanggal%")
            ->where('nama', 'like', "%$cari%")
            ->get();

        return view('rapat.cetak', [
            'rapat' => $rapat,
            'hari' => $this->hariIndo(date("l", strtotime($tanggal))),
            'tanggal' => $this->tgl_indo($tanggal),
            'jumlah' => count($rapat)
        ]);
    }

    public function hariIndo($hari)
    {
        switch ($hari) {
            case 'Sunday':
                return 'Minggu';
            case 'Monday':
                return 'Senin';
            case 'Tuesday':
                return 'Selasa';
            case 'Wednesday':
                return 'Rabu';
            case 'Thursday':
                return 'Kamis';
            case 'Friday':
                return 'Jumat';
            case 'Saturday':
                return 'Sabtu';
            default:
                return 'Tidak ada';
        }
    }

    public function tgl_indo($tanggal)
    {
        $bulan = array(
            1 =>   'JANUARI',
            'FEBRUARI',
            'MARET',
            'APRIL',
            'MEI',
            'JUNI',
            'JULI',
            'AGUSTUS',
            'SEPTEMBER',
            'OKTOBER',
            'NOVEMBER',
            'DESEMBER'
        );
        $pecahkan = explode('-', $tanggal);

        // variabel pecahkan 0 = tanggal
        // variabel pecahkan 1 = bulan
        // variabel pecahkan 2 = tahun

        return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
    }
}
