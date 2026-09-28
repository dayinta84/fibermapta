<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Models\TitikJaringan;
use Illuminate\Http\Request;

class TitikJaringanController extends Controller
{

    public function index()
    {
        
        $titik = TitikJaringan::with([
            'parent',
            'ports',
            'connectedFromPort',
        ])
            ->orderBy('jenis')
            ->orderBy('nama')
            ->get();


        $listOlt = TitikJaringan::olt()
            ->orderBy('nama')
            ->get();


        $listOdc = TitikJaringan::with('portOutKosong')
            ->odc()
            ->orderBy('nama')
            ->get();


        $portOutOdcKosong = Port::with('titikJaringan')
            ->out()
            ->kosong()
            ->milikOdc()
            ->orderBy('titik_jaringan_id')
            ->orderBy('nomor_port')
            ->get();

        return view(
            'administrator.titik.index',
            compact(
                'titik',
                'listOlt',
                'listOdc',
                'portOutOdcKosong'
            )
        );
    }


    public function store(Request $request)
    {

        $data = $request->validate(
            $this->rules()
        );

        TitikJaringan::createWithNetwork($data);

        return redirect()
            ->route('titik.index')
            ->with(
                'success',
                'Titik jaringan berhasil ditambahkan.'
            );
    }


    public function update(
        Request $request,
        TitikJaringan $titik
    ) {

        $data = $request->validate(
            $this->rules()
        );


        $titik->updateWithNetwork($data);

        return redirect()
            ->route('titik.index')
            ->with(
                'success',
                'Titik jaringan berhasil diperbarui.'
            );
    }


    public function destroy(TitikJaringan $titik)
    {

        $reason = $titik->deletionBlockReason();

        if ($reason !== null) {
            return redirect()
                ->route('titik.index')
                ->with(
                'error, titik masih memiliki turunan atau pelanggan',
                    $reason
                );
        }


        $titik->deleteWithNetwork();

        return redirect()
            ->route('titik.index')
            ->with(
                'success',
                'Titik jaringan berhasil dihapus.'
            );
    }


    public function showOdc(TitikJaringan $titik)
    {

        if (!$titik->isOdc()) {
            return redirect()
                ->route('titik.index')
                ->with(
                    'error',
                    'Halaman detail hanya untuk titik jenis ODC.'
                );
        }

        return view(
            'administrator.titik.detail_odc',
            $titik->odcDetailData()
        );
    }



    public function showOdp(TitikJaringan $titik)
    {

        if (!$titik->isOdp()) {
            return redirect()
                ->route('titik.index')
                ->with(
                    'error',
                    'Halaman detail hanya untuk titik jenis ODP.'
                );
        }

        return view(
            'administrator.titik.detail_odp',
            $titik->odpDetailData()
        );
    }


    private function rules(): array
    {
        return [
            'jenis' => [
                'required',
                'string',
                'in:OLT,ODC,ODP',
            ],

            'nama' => [
                'required',
                'string',
                'max:20',
            ],

            'lat' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'lng' => [
                'required',
                'numeric',
                'between:-180,180',
            ],

            'keterangan' => [
                'nullable',
                'string',
            ],

            'rasio_splitter' => [
                'nullable',
                'in:1:4,1:8,1:16',
            ],

            'parent_id' => [
                'nullable',
                'exists:titik_jaringan,id',
            ],

            'port_out_odc_id' => [
                'nullable',
                'exists:ports,id',
            ],

            'tx_power' => [
                'nullable',
                'numeric',
                'min:-100',
                'max:100',
            ],

            'panjang_kabel_meter' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ];
    }
}
