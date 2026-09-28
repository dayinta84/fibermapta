<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Models\TitikJaringan;
use Illuminate\Http\Request;

class PortController extends Controller
{

    public function generate(TitikJaringan $titik)
    {
        $titik->generatePorts();

        return back()->with(
            'success',
            'Port berhasil digenerate dan disesuaikan dengan rasio splitter.'
        );
    }


    public function updateRedaman(
        Request $request,
        Port $port
    ) {

        $data = $request->validate([
            'redaman_manual' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'sumber_port' => [
                'nullable',
                'string',
                'max:30',
            ],

            'keterangan_redaman' => [
                'nullable',
                'string',
            ],
        ]);



        $port->updateRedamanByRole(
            $data,
            auth()->user()->role ?? null
        );

        return back()->with(
            'success',
            'Informasi redaman port berhasil disimpan.'
        );
    }
}
