<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>REKAP ABSENSI RAPAT RSB NGANJUK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
  </head>
  <style>
        html, body {
            width:  210mm;
            height: 297mm;
        }
        table {
        page-break-inside: auto;
        }
        tr {
        page-break-inside: avoid;
        page-break-after: auto;
        }
        thead {
        display: table-header-group;
        }
        tfoot {
        display: table-footer-group;
        }
  </style>
  <body class="fw-bold">
    
       <div class="row">
            <div class="col-5 text-center fw-bold" style="font-size:12px;">
              POLRI DAERAH JAWA TIMUR<br>
              BIDANG KEDOKTERAN DAN KESEHATAN<br>
              <u>RUMAH SAKIT BHAYANGKARA TK. III NGANJUK</u>
            </div>
            <div class="col">
            </div>
            <div class="col">
            </div>
        </div>
        <div class="row p-3" style="font-size:12px;">
            <div class="col-12 text-center">DAFTAR HADIR</div>
        </div>
        <div class="row" style="font-size:12px;">
            <div class="col-2">
                HARI
            </div>
            <div class="col-4">
                : {{ $hari }}
            </div>
            <div class="col-1">
                ACARA
            </div>
            <div class="col-5">
                : .......................................................
            </div>
        </div>
        <div class="row" style="font-size:12px;">
            <div class="col-2">
                TANGGAL
            </div>
            <div class="col-4">
                : {{ $tanggal }}
            </div>
            <div class="col-1">
                JUMLAH
            </div>
            <div class="col-5">
                : {{ $jumlah }}
            </div>
        </div>
        <div class="row pt-3">
            <div class="col-12">
                <table class="table table-bordered border-dark" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:10%">NO</th>
                            <th class="text-center" style="width:30%">NAMA</th>
                            <th class="text-center" style="width:30%">NRP/INSTANSI/JABATAN</th>
                            <th class="text-center" colspan="2" style="width:30%">HADIR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rapat as $key => $value)
                            @php
                                $no = $key + 1;
                            @endphp
                        <tr>
                            <td> {{ $no }} </td>
                            <td> {{ $value->nama }} </td>
                            <td> {{ $value->instansi }} </td>
                            <td> {{ $value->nama }} </td>
                            <td style="width:15%">
                                @if($no % 2 != 0)
                                    <img src="{{ $value->tanda_tangan }}" alt="" width="80" height="50" />
                                @endif
                            </td style="width:15%">
                            <td>
                                @if($no % 2 == 0)
                                    <img src="{{ $value->tanda_tangan }}" alt="" width="80" height="50" />
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row pt-3">
            <div class="col-6 text-center" style="font-size:12px;">
                Mengetahui<br>
                KEPALA RUMAH SAKIT BHAYANGKARA TK.III NGANJUK<br><br><br><br><br>
                dr. LUSIANTO MADYO NUGROHO M.M.Kes<br>
                <SPAN STYLE="text-decoration:overline">AJUN KOMISARIS BESAR POLISI NRP 72010480</SPAN>
            </div>
            <div class="col-6 text-center" style="font-size:12px;">
                PEMIMPIN ACARA<br>
                <br><br><br><br><br>
                ........................................<br>
                
            </div>
        </div>
    <script>
		window.print();
	</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
  </body>
</html>