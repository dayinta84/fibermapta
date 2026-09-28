<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Port;
use App\Models\TitikJaringan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{

    public function index()
    {

        $pelanggan = Pelanggan::with([
            'port.titikJaringan.parent',
        ])
            ->orderBy('nama')
            ->get();


        $listOdp = TitikJaringan::with([
            'portOutKosong',
        ])
            ->where('jenis', 'ODP')
            ->orderBy('nama')
            ->get();


        $portOutOdpKosong = Port::with('titikJaringan')
            ->out()
            ->kosong()
            ->milikOdp()
            ->orderBy('titik_jaringan_id')
            ->orderBy('nomor_port')
            ->get();

        return view(
            'administrator.pelanggan.index',
            compact(
                'pelanggan',
                'listOdp',
                'portOutOdpKosong'
            )
        );
    }


    public function store(Request $request)
    {
        $data = $request->validate(
            $this->rules()
        );

        Pelanggan::createWithPort($data);

        return back()->with(
            'success',
            'Pelanggan berhasil ditambahkan dan port ODP sudah ditandai terpakai.'
        );
    }


    public function update(
        Request $request,
        Pelanggan $pelanggan
    ) {
        $data = $request->validate(
            $this->rules(false)
        );

        $pelanggan->updateWithPort($data);

        return back()->with(
            'success',
            'Data pelanggan berhasil diperbarui.'
        );
    }


    public function destroy(Pelanggan $pelanggan)
    {
        $pelanggan->deleteWithPort();

        return back()->with(
            'success',
            'Pelanggan berhasil dihapus dan port dikembalikan menjadi kosong.'
        );
    }

    //form
    private function rules(
        bool $portRequired = true
    ): array {
        return [
            'nama' => [
                'required',
                'string',
                'max:50',
            ],

            'no_layanan' => [
                'nullable',
                'string',
                'max:20',
            ],

            'alamat' => [
                'nullable',
                'string',
            ],

            'lat' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'lng' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'keterangan' => [
                'nullable',
                'string',
            ],

            'odp_id' => [
                'required',
                'exists:titik_jaringan,id',
            ],

            'port_id' => [
                $portRequired ? 'required' : 'nullable',
                'exists:ports,id',
            ],
        ];
    }
}
