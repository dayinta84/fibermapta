<?php

namespace App\Http\Controllers;

use App\Models\SimulasiTopologiPlb;
use Illuminate\Http\Request;

class SimulasiTopologiPlbController extends Controller
{

    public function index()
    {
        $simulasi = SimulasiTopologiPlb::latest()->get();

        return view(
            'administrator.simulasi_topologi_plb.index',
            compact('simulasi')
        );
    }


    public function store(Request $request)
    {

        $data = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:50',
            ],

            'catatan' => [
                'nullable',
                'string',
            ],

            'ptx' => [
                'required',
                'numeric',
            ],

            'threshold_rx' => [
                'required',
                'numeric',
            ],
        ]);


        $simulasi =
            SimulasiTopologiPlb::createDefault($data);

        return redirect()
            ->route(
                'simulasi-topologi-plb.show',
                $simulasi->id
            )
            ->with(
                'success',
                'Simulasi topologi berhasil dibuat.'
            );
    }


    
    public function show(
        SimulasiTopologiPlb $simulasi
    ) {
        return view(
            'administrator.simulasi_topologi_plb.show',
            compact('simulasi')
        );
    }


    
    public function updateSettings(
        Request $request,
        SimulasiTopologiPlb $simulasi
    ) {

        $data = $request->validate([
            'ptx' => [
                'required',
                'numeric',
            ],

            'alpha_serat' => [
                'required',
                'numeric',
                'min:0',
            ],

            'loss_konektor' => [
                'required',
                'numeric',
                'min:0',
            ],

            'loss_splice' => [
                'required',
                'numeric',
                'min:0',
            ],

            'safety_margin' => [
                'required',
                'numeric',
                'min:0',
            ],

            'threshold_rx' => [
                'required',
                'numeric',
            ],

            'splitter_losses' => [
                'required',
                'array',
            ],

            'splitter_losses.*' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);


        $simulasi->updateSettings($data);

        return back()->with(
            'success',
            'Pengaturan simulasi berhasil disimpan.'
        );
    }


    public function updateTopology(
        Request $request,
        SimulasiTopologiPlb $simulasi
    ) {
        $data = $request->validate([
            'nodes' => [
                'required',
                'array',
            ],

            'edges' => [
                'required',
                'array',
            ],
        ]);


        $simulasi->saveTopology(
            $data['nodes'],
            $data['edges']
        );

        return response()->json([
            'status' => true,
            'message' => 'Topologi berhasil disimpan.',
        ]);
    }


    public function hitung(
        Request $request,
        SimulasiTopologiPlb $simulasi
    ) {

        $data = $request->validate([
            'nodes' => [
                'nullable',
                'array',
            ],

            'edges' => [
                'nullable',
                'array',
            ],
        ]);


        $result = $simulasi->calculateTopology(
            $data['nodes'] ?? null,
            $data['edges'] ?? null
        );


        $httpStatus =
            ($result['status'] ?? false)
            ? 200
            : 422;

        return response()->json(
            $result,
            $httpStatus
        );
    }


    public function destroy(
        SimulasiTopologiPlb $simulasi
    ) {
        $simulasi->delete();

        return redirect()
            ->route('simulasi-topologi-plb.index')
            ->with(
                'success',
                'Simulasi berhasil dihapus.'
            );
    }
}
