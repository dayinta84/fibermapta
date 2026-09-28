<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\TitikJaringan;
use Illuminate\Http\JsonResponse;

class MapDataController extends Controller
{
    public function index(): JsonResponse
    {
        $titik = TitikJaringan::query()
            ->with([
                'parent:id,jenis,nama,lat,lng',
            ])
            ->select([
                'id',
                'jenis',
                'nama',
                'lat',
                'lng',
                'keterangan',
                'rasio_splitter',
                'parent_id',
                'kapasitas_port',
                'tx_power',
                'jenis_jalur',
                'jarak_udara_meter',
                'panjang_kabel_meter',
            ])
            ->get()
            ->map(
                fn(TitikJaringan $item) =>
                $item->toMapPayload()
            )
            ->values();


        $pelanggan = Pelanggan::query()
            ->with([
                'port:id,titik_jaringan_id,jenis_port,nomor_port,status',

                'port.titikJaringan:id,jenis,nama,lat,lng,rasio_splitter',
            ])
            ->select([
                'id',
                'port_id',
                'nama',
                'no_layanan',
                'alamat',
                'lat',
                'lng',
                'keterangan',
            ])
            ->get()
            ->map(
                fn(Pelanggan $item) =>
                $item->toMapPayload()
            )
            ->values();


        return response()->json([
            'titik' => $titik,
            'pelanggan' => $pelanggan,
        ]);
    }
}
