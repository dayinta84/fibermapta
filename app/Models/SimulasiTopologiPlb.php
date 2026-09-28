<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulasiTopologiPlb extends Model
{

    private const ALLOWED_SPLITTER_RATIOS = [
        '1:4',
        '1:8',
        '1:16',
    ];


    protected $table = 'simulasi_topologi_plb';

    protected $fillable = [
        'nama',
        'catatan',
        'ptx',
        'alpha_serat',
        'loss_konektor',
        'loss_splice',
        'safety_margin',
        'threshold_rx',
        'splitter_losses',
        'nodes',
        'edges',
        'hasil',
    ];

    protected $casts = [
        'ptx' => 'float',
        'alpha_serat' => 'float',
        'loss_konektor' => 'float',
        'loss_splice' => 'float',
        'safety_margin' => 'float',
        'threshold_rx' => 'float',
        'splitter_losses' => 'array',
        'nodes' => 'array',
        'edges' => 'array',
        'hasil' => 'array',
    ];



    public static function createDefault(array $data): self
    {
        $ptx = (float) $data['ptx'];

        return static::create([
            'nama' => $data['nama'],

            'catatan' =>
            $data['catatan'] ?? null,

            'ptx' => $ptx,

            'threshold_rx' =>
            (float) $data['threshold_rx'],


            //nilai awal
            'alpha_serat' => 0.35,
            'loss_konektor' => 0.25,
            'loss_splice' => 0.10,
            'safety_margin' => 3,

            'splitter_losses' =>
            static::defaultSplitterLosses(),


            'nodes' => [
                [
                    'id' => 'node_olt_1',
                    'type' => 'OLT',
                    'label' => 'Main OLT',
                    'x' => 260,
                    'y' => 90,
                    'ptx' => $ptx,
                    'prx' => $ptx,
                    'total_loss' => 0,
                    'status' => 'source',
                ],
            ],

            'edges' => [],
            'hasil' => [],
        ]);
    }


    
    public function updateSettings(array $data): self
    {
        $this->update([
            'ptx' =>
            $data['ptx'],

            'alpha_serat' =>
            $data['alpha_serat'],

            'loss_konektor' =>
            $data['loss_konektor'],

            'loss_splice' =>
            $data['loss_splice'],

            'safety_margin' =>
            $data['safety_margin'],

            'threshold_rx' =>
            $data['threshold_rx'],

            'splitter_losses' =>
            static::sanitizeSplitterLosses(
                $data['splitter_losses'] ?? []
            ),
        ]);

        return $this->refresh();
    }


    //simpan
    public function saveTopology(
        array $nodes,
        array $edges
    ): self {
        $this->update([

            'nodes' =>
            static::normalizeNodes($nodes),

            'edges' => $edges,
        ]);

        return $this->refresh();
    }


    public function calculateTopology(
        ?array $nodes = null,
        ?array $edges = null
    ): array {

        $nodes = static::normalizeNodes(
            $nodes ?? $this->nodes ?? []
        );

        $edges =
            $edges ?? $this->edges ?? [];


        $validation =
            static::validateTopology(
                $nodes,
                $edges
            );

        if (!$validation['status']) {
            return $validation;
        }


        $calculation =
            $this->processCalculation(
                $nodes,
                $edges
            );


        if (empty($calculation['hasil'])) {
            return [
                'status' => false,

                'message' =>
                'Belum ada endpoint ONU/Pelanggan yang terhubung dari OLT. Tambahkan ONU/Pelanggan dan hubungkan jalurnya terlebih dahulu.',
            ];
        }


        $this->update([
            'nodes' =>
            $calculation['nodes'],

            'edges' =>
            $calculation['edges'],

            'hasil' =>
            $calculation['hasil'],
        ]);

        return [
            'status' => true,
            'message' => 'Perhitungan berhasil.',

            ...$calculation,
        ];
    }



    private static function validateTopology(
        array $nodes,
        array $edges
    ): array {

        if (
            collect($nodes)
            ->where('type', 'OLT')
            ->count() < 1
        ) {
            return [
                'status' => false,

                'message' =>
                'Topologi harus memiliki minimal 1 OLT.',
            ];
        }

        if (
            collect($nodes)
            ->where('type', 'ONU')
            ->count() < 1
        ) {
            return [
                'status' => false,

                'message' =>
                'Topologi harus memiliki minimal 1 ONU/Pelanggan sebagai endpoint.',
            ];
        }


        foreach ($nodes as $node) {
            if (
                ($node['type'] ?? null)
                !== 'Splitter'
            ) {
                continue;
            }

            $ratio =
                static::normalizeSplitterRatio(
                    $node['ratio'] ?? '1:4'
                );

            $maxPort =
                static::splitterOutputCount(
                    $ratio
                );


            $usedPorts = collect($edges)
                ->where(
                    'from',
                    $node['id']
                )
                ->pluck('from_port')
                ->filter()
                ->unique()
                ->values();


            if (
                $usedPorts->count() > $maxPort
            ) {
                return [
                    'status' => false,

                    'message' =>
                    "Splitter {$node['label']} hanya memiliki {$maxPort} output.",
                ];
            }


            foreach ($usedPorts as $portName) {
                $portNumber = (int) str_replace(
                    'P',
                    '',
                    $portName
                );

                if (
                    $portNumber < 1
                    || $portNumber > $maxPort
                ) {
                    return [
                        'status' => false,

                        'message' =>
                        "Port {$portName} tidak tersedia pada splitter {$node['label']} dengan rasio {$ratio}.",
                    ];
                }
            }
        }

        return [
            'status' => true,
            'message' => 'Valid.',
        ];
    }


    
    private function processCalculation(
        array $nodes,
        array $edges
    ): array {
        $nodeMap = [];
        $childrenMap = [];


        foreach ($nodes as $node) {

            $node['prx'] = null;
            $node['total_loss'] = null;
            $node['margin'] = null;
            $node['status'] = 'belum_dihitung';

            $nodeMap[$node['id']] = $node;
        }


        foreach ($edges as $edge) {
            $childrenMap[$edge['from']][] = $edge;
        }

        $hasil = [];


        foreach (
            $nodeMap as $nodeId => $node
        ) {
            if (
                ($node['type'] ?? null)
                !== 'OLT'
            ) {
                continue;
            }

            $ptx =
                isset($node['ptx'])
                && $node['ptx'] !== null
                ? (float) $node['ptx']
                : (float) $this->ptx;


            $nodeMap[$nodeId]['prx'] =
                $ptx;

            $nodeMap[$nodeId]['total_loss'] =
                0;

            $nodeMap[$nodeId]['status'] =
                'source';


            $this->dfsCalculate(
                $nodeId,
                $ptx,
                0,
                0,
                [$nodeMap[$nodeId]['label']],
                $nodeMap,
                $childrenMap,
                $edges,
                $hasil,
                []
            );
        }

        return [
            'nodes' =>
            array_values($nodeMap),

            'edges' =>
            array_values($edges),

            'hasil' =>
            $hasil,
        ];
    }


    
    private function dfsCalculate(
        string $currentNodeId,
        float $currentPower,
        float $currentLoss,
        float $currentLength,
        array $path,
        array &$nodeMap,
        array $childrenMap,
        array &$edges,
        array &$results,
        array $visited
    ): void {

        if (
            in_array(
                $currentNodeId,
                $visited,
                true
            )
        ) {
            return;
        }

        $visited[] = $currentNodeId;

        
        $outEdges =
            $childrenMap[$currentNodeId] ?? [];

        foreach ($outEdges as $edge) {
            $toNodeId = $edge['to'];


            if (!isset($nodeMap[$toNodeId])) {
                continue;
            }


            $edgeLoss =
                $this->calculateEdgeLoss($edge);


            $nodeLoss =
                $this->calculateNodeLoss(
                    $nodeMap[$toNodeId]
                );


            $nextPowerWithoutMargin =
                $currentPower
                - $edgeLoss
                - $nodeLoss;

            $nextLossWithoutMargin =
                $currentLoss
                + $edgeLoss
                + $nodeLoss;


            $nextLength =
                $currentLength
                + (float) (
                    $edge['panjang_km'] ?? 0
                );


            $nextPath = array_merge(
                $path,
                [
                    $nodeMap[$toNodeId]['label'],
                ]
            );


            $isEndpoint =
                (
                    $nodeMap[$toNodeId]['type'] ?? null
                ) === 'ONU';

            $safetyMargin =
                $isEndpoint
                ? (float) $this->safety_margin
                : 0.0;


            $finalPower =
                $nextPowerWithoutMargin
                - $safetyMargin;

            $finalLoss =
                $nextLossWithoutMargin
                + $safetyMargin;


            $threshold =
                isset(
                    $nodeMap[$toNodeId]['threshold_rx']
                )
                &&
                $nodeMap[$toNodeId]['threshold_rx'] !== null
                ? (float) $nodeMap[$toNodeId]['threshold_rx']
                : (float) $this->threshold_rx;

            //Margin = selisih daya terima dengan sensitivitas receiver.
            $margin =
                $finalPower - $threshold;

            $status =
                $finalPower >= $threshold
                ? 'layak'
                : 'tidak_layak';


            $nodeMap[$toNodeId]['prx'] =
                round($finalPower, 3);

            $nodeMap[$toNodeId]['total_loss'] =
                round($finalLoss, 3);

            $nodeMap[$toNodeId]['margin'] =
                round($margin, 3);

            $nodeMap[$toNodeId]['status'] =
                $status;



            if ($isEndpoint) {
                $results[] = [
                    'endpoint' =>
                    $nodeMap[$toNodeId]['label'],

                    'jalur' =>
                    implode(
                        ' → ',
                        $nextPath
                    ),

                    'total_panjang_km' =>
                    round($nextLength, 3),

                    'total_loss' =>
                    round($finalLoss, 3),

                    'safety_margin' =>
                    round($safetyMargin, 3),

                    'prx' =>
                    round($finalPower, 3),

                    'threshold_rx' =>
                    round($threshold, 3),

                    'margin' =>
                    round($margin, 3),

                    'status' =>
                    $status,
                ];
            }


            $this->dfsCalculate(
                $toNodeId,
                $nextPowerWithoutMargin,
                $nextLossWithoutMargin,
                $nextLength,
                $nextPath,
                $nodeMap,
                $childrenMap,
                $edges,
                $results,
                $visited
            );
        }
    }


    private function calculateEdgeLoss(
        array $edge
    ): float {
        $lengthKm =
            (float) (
                $edge['panjang_km'] ?? 0
            );


        $fiberAlpha =
            isset($edge['alpha_serat'])
            && $edge['alpha_serat'] !== null
            ? (float) $edge['alpha_serat']
            : (float) $this->alpha_serat;

        $connectorCount =
            (int) (
                $edge['jumlah_konektor'] ?? 0
            );

        $connectorLoss =
            isset($edge['loss_konektor'])
            && $edge['loss_konektor'] !== null
            ? (float) $edge['loss_konektor']
            : (float) $this->loss_konektor;

        $spliceCount =
            (int) (
                $edge['jumlah_splice'] ?? 0
            );

        $spliceLoss =
            isset($edge['loss_splice'])
            && $edge['loss_splice'] !== null
            ? (float) $edge['loss_splice']
            : (float) $this->loss_splice;


        $lossKabel =
            $lengthKm * $fiberAlpha;

        $lossConnectorTotal =
            $connectorCount * $connectorLoss;

        $lossSpliceTotal =
            $spliceCount * $spliceLoss;

        return
            $lossKabel
            + $lossConnectorTotal
            + $lossSpliceTotal;
    }


    
    private function calculateNodeLoss(
        array $node
    ): float {

        if (
            ($node['type'] ?? null)
            !== 'Splitter'
        ) {
            return 0;
        }


        if (
            isset($node['splitter_loss'])
            && $node['splitter_loss'] !== null
            && $node['splitter_loss'] !== ''
        ) {
            return (float)
            $node['splitter_loss'];
        }


        $ratio =
            static::normalizeSplitterRatio(
                $node['ratio'] ?? '1:4'
            );

        $losses =
            static::sanitizeSplitterLosses(
                $this->splitter_losses ?? []
            );

        return (float) (
            $losses[$ratio] ?? 0
        );
    }



    private static function normalizeNodes(
        array $nodes
    ): array {
        return collect($nodes)
            ->map(function (array $node) {
                if (
                    ($node['type'] ?? null)
                    === 'Splitter'
                ) {
                    $ratio =
                        static::normalizeSplitterRatio(
                            $node['ratio'] ?? '1:4'
                        );

                    $node['ratio'] = $ratio;

                    $node['splitter_loss'] =
                        isset($node['splitter_loss'])
                        &&
                        $node['splitter_loss'] !== null
                        &&
                        $node['splitter_loss'] !== ''
                        ? (float)
                        $node['splitter_loss']
                        : static::defaultSplitterLosses()[$ratio];
                }

                return $node;
            })
            ->values()
            ->toArray();
    }


    private static function normalizeSplitterRatio(
        string $ratio
    ): string {
        return in_array(
            $ratio,
            self::ALLOWED_SPLITTER_RATIOS,
            true
        )
            ? $ratio
            : '1:4';
    }


    private static function splitterOutputCount(
        string $ratio
    ): int {
        return match (static::normalizeSplitterRatio($ratio)) {
            '1:4' => 4,
            '1:8' => 8,
            '1:16' => 16,
            default => 4,
        };
    }


    
    public static function defaultSplitterLosses(): array
    {
        return [
            '1:4' => 7.3,
            '1:8' => 10.5,
            '1:16' => 13.8,
        ];
    }


    private static function sanitizeSplitterLosses(
        array $losses
    ): array {
        $defaults =
            static::defaultSplitterLosses();

        $sanitized = [];

        foreach (
            self::ALLOWED_SPLITTER_RATIOS
            as $ratio
        ) {
            $sanitized[$ratio] =
                isset($losses[$ratio])
                && $losses[$ratio] !== null
                && $losses[$ratio] !== ''
                ? (float) $losses[$ratio]
                : $defaults[$ratio];
        }

        return $sanitized;
    }
}
