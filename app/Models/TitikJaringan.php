<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TitikJaringan extends Model
{

    public const JENIS_OLT = 'OLT';
    public const JENIS_ODC = 'ODC';
    public const JENIS_ODP = 'ODP';


    protected $table = 'titik_jaringan';

    protected $fillable = [
        'jenis',
        'nama',
        'lat',
        'lng',
        'keterangan',
        'rasio_splitter',
        'parent_id',
        'kapasitas_port',
        'tx_power',
        'jarak_udara_meter',
        'panjang_kabel_meter',
        'jenis_jalur',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
        'kapasitas_port' => 'integer',
        'tx_power' => 'float',
        'jarak_udara_meter' => 'float',
        'panjang_kabel_meter' => 'float',
    ];


    public function parent()
    {
        return $this->belongsTo(
            TitikJaringan::class,
            'parent_id'
        );
    }


    public function children()
    {
        return $this->hasMany(
            TitikJaringan::class,
            'parent_id'
        );
    }


    public function ports()
    {
        return $this->hasMany(
            Port::class,
            'titik_jaringan_id'
        )
            ->orderBy('jenis_port')
            ->orderBy('nomor_port');
    }


    public function portIn()
    {
        return $this->hasOne(
            Port::class,
            'titik_jaringan_id'
        )
            ->where(
                'jenis_port',
                Port::JENIS_IN
            );
    }


    public function portOuts()
    {
        return $this->hasMany(
            Port::class,
            'titik_jaringan_id'
        )
            ->where(
                'jenis_port',
                Port::JENIS_OUT
            )
            ->orderBy('nomor_port');
    }


    public function portOutKosong()
    {
        return $this->hasMany(
            Port::class,
            'titik_jaringan_id'
        )
            ->where(
                'jenis_port',
                Port::JENIS_OUT
            )
            ->where(
                'status',
                Port::STATUS_KOSONG
            )
            ->orderBy('nomor_port');
    }


    public function connectedFromPort()
    {
        return $this->hasOne(
            Port::class,
            'connected_titik_id'
        );
    }



    public function scopeOlt(Builder $query): Builder
    {
        return $query->where(
            'jenis',
            self::JENIS_OLT
        );
    }

    public function scopeOdc(Builder $query): Builder
    {
        return $query->where(
            'jenis',
            self::JENIS_ODC
        );
    }

    public function scopeOdp(Builder $query): Builder
    {
        return $query->where(
            'jenis',
            self::JENIS_ODP
        );
    }


    public function isOlt(): bool
    {
        return $this->jenis === self::JENIS_OLT;
    }

    public function isOdc(): bool
    {
        return $this->jenis === self::JENIS_ODC;
    }

    public function isOdp(): bool
    {
        return $this->jenis === self::JENIS_ODP;
    }



    public static function kapasitasByRasio(
        ?string $rasio
    ): int {
        return match ($rasio) {
            '1:4' => 4,
            '1:8' => 8,
            '1:16' => 16,
            default => 0,
        };
    }



    public static function createWithNetwork(
        array $data
    ): self {
        return DB::transaction(function () use ($data) {

            $sourcePort = static::validateAndResolveSourcePort(
                $data,
                null,
                true
            );

            $attributes = static::prepareAttributes($data);

            $titik = static::create($attributes);

            if ($titik->isOdc() || $titik->isOdp()) {
                $titik->syncPorts();
            }

            if ($titik->isOdp() && $sourcePort) {
                $titik->attachToOdcPort($sourcePort);
            }

            return $titik->refresh();
        });
    }



    public function updateWithNetwork(
        array $data
    ): self {
        return DB::transaction(function () use ($data) {

            $this->refresh();

            $sourcePort = static::validateAndResolveSourcePort(
                $data,
                $this,
                false
            );

            $oldJenis = $this->jenis;
            $newJenis = $data['jenis'];

            if (
                $oldJenis === self::JENIS_ODP
                && $newJenis !== self::JENIS_ODP
            ) {
                $this->detachFromOdcPort();
            }

            if (
                $oldJenis === self::JENIS_ODC
                && $newJenis !== self::JENIS_ODC
            ) {
                $this->releaseConnectedOdpPorts();
            }

            $this->update(
                static::prepareAttributes($data)
            );

            $this->refresh();


            if ($this->isOlt()) {
                $this->deletePortsIfSafe();
            }


            if ($this->isOdc() || $this->isOdp()) {
                $this->syncPorts();
            }


            if ($this->isOdp() && $sourcePort) {
                $this->detachFromOdcPort(
                    $sourcePort->id
                );

                $this->attachToOdcPort(
                    $sourcePort
                );
            }

            return $this->refresh();
        });
    }


    public function deletionBlockReason(): ?string
    {

        if ($this->children()->exists()) {
            return 'Titik tidak bisa dihapus karena masih memiliki turunan.';
        }

        if (
            $this->ports()
            ->whereHas('pelanggan')
            ->exists()
        ) {
            return 'Titik tidak bisa dihapus karena masih memiliki pelanggan pada port.';
        }

        return null;
    }


    public function deleteWithNetwork(): void
    {
        DB::transaction(function () {
            $this->refresh();

            $reason = $this->deletionBlockReason();

            if ($reason !== null) {
                throw ValidationException::withMessages([
                    'titik' => $reason,
                ]);
            }


            if ($this->isOdp()) {
                $this->detachFromOdcPort();
            }

            if ($this->isOdc()) {
                $this->releaseConnectedOdpPorts();
            }

            $this->delete();
        });
    }



    public function generatePorts(): void
    {
        if ($this->isOlt()) {
            throw ValidationException::withMessages([
                'titik' =>
                'OLT adalah sumber utama jaringan dan tidak perlu dibuatkan port.',
            ]);
        }

        if (!$this->isOdc() && !$this->isOdp()) {
            throw ValidationException::withMessages([
                'titik' =>
                'Generate port hanya dapat dilakukan pada ODC atau ODP.',
            ]);
        }

        if (!$this->rasio_splitter) {
            throw ValidationException::withMessages([
                'rasio_splitter' =>
                'Rasio splitter belum diisi. Isi rasio splitter terlebih dahulu.',
            ]);
        }

        DB::transaction(function () {
            $this->syncPorts();
        });
    }


    public function syncPorts(): void
    {

        if ($this->isOlt()) {
            return;
        }

        $jumlahOut = static::kapasitasByRasio(
            $this->rasio_splitter
        );

        if ($jumlahOut <= 0) {
            throw ValidationException::withMessages([
                'rasio_splitter' =>
                'Rasio splitter tidak valid. Gunakan rasio 1:4, 1:8, atau 1:16.',
            ]);
        }


        $this->update([
            'kapasitas_port' => $jumlahOut,
        ]);

        $this->syncPortIn();

        $this->syncPortOut($jumlahOut);

        $this->deleteExtraEmptyOutPorts($jumlahOut);
    }



    public function odcDetailData(): array
    {
        $this->load([
            'parent',
            'portIn',
            'portOuts.connectedTitik',
        ]);

        $portOut = $this->portOuts;

        $jumlahPort = $portOut->count();

        $jumlahTerpakai = $portOut
            ->where(
                'status',
                Port::STATUS_TERPAKAI
            )
            ->count();

        $jumlahKosong = max(
            $jumlahPort - $jumlahTerpakai,
            0
        );

        return [
            'titik' => $this,
            'portIn' => $this->portIn,
            'portOut' => $portOut,

            'kapasitasPort' =>
            static::kapasitasByRasio(
                $this->rasio_splitter
            ),

            'jumlahPort' => $jumlahPort,
            'jumlahTerpakai' => $jumlahTerpakai,
            'jumlahKosong' => $jumlahKosong,
        ];
    }

    
    
    public function odpDetailData(): array
    {
        $this->load([
            'parent.parent',
            'portIn',
            'portOuts.pelanggan',
            'connectedFromPort.titikJaringan',
        ]);

        $portOut = $this->portOuts;

        $jumlahPort = $portOut->count();

        $jumlahTerpakai = $portOut
            ->filter(function (Port $port) {
                return $port->pelanggan !== null
                    || $port->status === Port::STATUS_TERPAKAI;
            })
            ->count();

        $jumlahKosong = max(
            $jumlahPort - $jumlahTerpakai,
            0
        );

        return [
            'titik' => $this,
            'portIn' => $this->portIn,
            'portOut' => $portOut,

            'kapasitasPort' =>
            static::kapasitasByRasio(
                $this->rasio_splitter
            ),

            'jumlahPort' => $jumlahPort,
            'jumlahTerpakai' => $jumlahTerpakai,
            'jumlahKosong' => $jumlahKosong,

            'isPenuh' =>
            $jumlahPort > 0
                && $jumlahKosong === 0,

            'sourcePort' => $this->connectedFromPort,
        ];
    }

    

    public function toMapPayload(): array
    {
        return [
            'id' => $this->id,
            'jenis' => $this->jenis,
            'nama' => $this->nama,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'keterangan' => $this->keterangan,
            'rasio_splitter' => $this->rasio_splitter,
            'parent_id' => $this->parent_id,
            'kapasitas_port' => $this->kapasitas_port,
            'tx_power' => $this->tx_power,
            'jenis_jalur' => $this->jenis_jalur,
            'jarak_udara_meter' => $this->jarak_udara_meter,
            'panjang_kabel_meter' => $this->panjang_kabel_meter,

            'parent' => $this->parent
                ? [
                    'id' => $this->parent->id,
                    'jenis' => $this->parent->jenis,
                    'nama' => $this->parent->nama,
                    'lat' => $this->parent->lat,
                    'lng' => $this->parent->lng,
                ]
                : null,
        ];
    }

    

    public function getJumlahPortOutAttribute(): ?int
    {
        $jumlah = static::kapasitasByRasio(
            $this->rasio_splitter
        );

        return $jumlah > 0
            ? $jumlah
            : null;
    }

    public function getIsOltAttribute(): bool
    {
        return $this->isOlt();
    }

    public function getIsOdcAttribute(): bool
    {
        return $this->isOdc();
    }

    public function getIsOdpAttribute(): bool
    {
        return $this->isOdp();
    }


    
    private static function validateAndResolveSourcePort(
        array $data,
        ?self $current,
        bool $creating
    ): ?Port {
        $jenis = $data['jenis'];

        $parentId = !empty($data['parent_id'])
            ? (int) $data['parent_id']
            : null;

        $rasio = $data['rasio_splitter'] ?? null;


        if (
            $current
            && $parentId !== null
            && $parentId === (int) $current->id
        ) {
            throw ValidationException::withMessages([
                'parent_id' =>
                'Titik tidak boleh menjadi parent untuk dirinya sendiri.',
            ]);
        }


        if (
            $current
            && $current->jenis !== $jenis
            && $current->children()->exists()
        ) {
            throw ValidationException::withMessages([
                'jenis' =>
                'Jenis titik tidak dapat diubah karena titik masih memiliki turunan.',
            ]);
        }


        if (
            $current
            && $current->jenis !== $jenis
            && $current->ports()
            ->whereHas('pelanggan')
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'jenis' =>
                'Jenis titik tidak dapat diubah karena masih terdapat pelanggan pada port.',
            ]);
        }


        if ($jenis === self::JENIS_OLT) {
            return null;
        }


        if (
            !in_array(
                $jenis,
                [
                    self::JENIS_ODC,
                    self::JENIS_ODP,
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'jenis' => 'Jenis titik tidak valid.',
            ]);
        }


        if ($parentId === null) {
            $parentName = $jenis === self::JENIS_ODC
                ? self::JENIS_OLT
                : self::JENIS_ODC;

            throw ValidationException::withMessages([
                'parent_id' =>
                "{$jenis} harus terhubung ke {$parentName}.",
            ]);
        }


        $expectedParentType =
            $jenis === self::JENIS_ODC
            ? self::JENIS_OLT
            : self::JENIS_ODC;

        $parent = static::query()->find($parentId);

        if (
            !$parent
            || $parent->jenis !== $expectedParentType
        ) {
            throw ValidationException::withMessages([
                'parent_id' =>
                "Parent untuk {$jenis} harus titik jenis {$expectedParentType}.",
            ]);
        }


        if (empty($rasio)) {
            throw ValidationException::withMessages([
                'rasio_splitter' =>
                "Rasio splitter harus diisi untuk {$jenis}.",
            ]);
        }


        $newCapacity = static::kapasitasByRasio(
            $rasio
        );

        if (
            $current
            && $newCapacity > 0
            && $current->hasUsedOutPortsBeyondCapacity(
                $newCapacity
            )
        ) {
            throw ValidationException::withMessages([
                'rasio_splitter' =>
                'Rasio splitter tidak dapat diturunkan karena terdapat port di luar kapasitas baru yang masih terpakai. Kosongkan port tersebut terlebih dahulu.',
            ]);
        }


        if ($jenis !== self::JENIS_ODP) {
            return null;
        }


        $requestedPortId =
            !empty($data['port_out_odc_id'])
            ? (int) $data['port_out_odc_id']
            : null;


        if ($requestedPortId !== null) {
            $sourcePort = Port::findAvailableForOdp(
                $requestedPortId,
                $parentId,
                $current?->id
            );

            if (!$sourcePort) {
                throw ValidationException::withMessages([
                    'port_out_odc_id' =>
                    'Port OUT ODC tidak valid, bukan milik ODC yang dipilih, atau sudah terpakai.',
                ]);
            }

            return $sourcePort;
        }


        if ($creating) {
            throw ValidationException::withMessages([
                'port_out_odc_id' =>
                'Saat membuat ODP, pilih Port OUT ODC yang digunakan.',
            ]);
        }


        $currentSourcePort =
            $current?->sourceOdcPort(true);

        if (!$currentSourcePort) {
            throw ValidationException::withMessages([
                'port_out_odc_id' =>
                'ODP harus memilih Port OUT ODC yang digunakan.',
            ]);
        }


        if (
            (int) $currentSourcePort->titik_jaringan_id
            !== $parentId
        ) {
            throw ValidationException::withMessages([
                'port_out_odc_id' =>
                'Jika mengganti ODC, pilih juga Port OUT ODC yang baru.',
            ]);
        }

        return null;
    }

    
    
    private static function prepareAttributes(
        array $data
    ): array {

        unset($data['port_out_odc_id']);


        if ($data['jenis'] === self::JENIS_OLT) {
            $data['parent_id'] = null;
            $data['rasio_splitter'] = null;
            $data['kapasitas_port'] = null;
            $data['jarak_udara_meter'] = null;
            $data['panjang_kabel_meter'] = null;
            $data['jenis_jalur'] = null;

            return $data;
        }

        $data['tx_power'] = null;

        $data['kapasitas_port'] =
            static::kapasitasByRasio(
                $data['rasio_splitter'] ?? null
            );

        return static::applyAutoRouteData($data);
    }


    
    private function sourceOdcPort(
        bool $lock = false
    ): ?Port {
        $query = Port::query()
            ->where(
                'connected_titik_id',
                $this->id
            )
            ->out()
            ->milikOdc();

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }


    private function attachToOdcPort(
        Port $port
    ): void {
        $port->markUsedByTitik($this);
    }


    private function detachFromOdcPort(
        ?int $exceptPortId = null
    ): void {
        Port::query()
            ->where(
                'connected_titik_id',
                $this->id
            )
            ->out()
            ->milikOdc()
            ->when(
                $exceptPortId !== null,
                function (
                    Builder $query
                ) use ($exceptPortId) {
                    $query->where(
                        'id',
                        '!=',
                        $exceptPortId
                    );
                }
            )
            ->get()
            ->each(function (Port $port) {
                $port->releaseConnection();
            });
    }


    private function releaseConnectedOdpPorts(): void
    {
        $this->ports()
            ->out()
            ->whereNotNull('connected_titik_id')
            ->get()
            ->each(function (Port $port) {
                $port->releaseConnection();
            });
    }


    
    private function syncPortIn(): void
    {
        $sumberPort = $this->parent?->nama;

        $keterangan = match ($this->jenis) {
            self::JENIS_ODC =>
            'Port masuk dari OLT',

            self::JENIS_ODP =>
            'Port masuk dari ODC',

            default =>
            'Port masuk dari sumber sebelumnya',
        };

        Port::updateOrCreate(
            [
                'titik_jaringan_id' => $this->id,
                'jenis_port' => Port::JENIS_IN,
                'nomor_port' => 1,
            ],
            [
                'status' => Port::STATUS_TERPAKAI,
                'sumber_port' => $sumberPort,
                'keterangan_redaman' => $keterangan,
            ]
        );
    }


    
    private function syncPortOut(
        int $jumlahOut
    ): void {
        for (
            $nomor = 1;
            $nomor <= $jumlahOut;
            $nomor++
        ) {
            Port::firstOrCreate(
                [
                    'titik_jaringan_id' => $this->id,
                    'jenis_port' => Port::JENIS_OUT,
                    'nomor_port' => $nomor,
                ],
                [
                    'status' => Port::STATUS_KOSONG,
                    'redaman_manual' => null,
                    'sumber_port' => null,
                    'keterangan_redaman' => null,
                    'connected_titik_id' => null,
                ]
            );
        }
    }


    
    private function deleteExtraEmptyOutPorts(
        int $jumlahOut
    ): void {
        $this->ports()
            ->out()
            ->where(
                'nomor_port',
                '>',
                $jumlahOut
            )
            ->kosong()
            ->whereNull('connected_titik_id')
            ->doesntHave('pelanggan')
            ->delete();
    }


    private function hasUsedOutPortsBeyondCapacity(
        int $newCapacity
    ): bool {
        return $this->ports()
            ->out()
            ->where(
                'nomor_port',
                '>',
                $newCapacity
            )
            ->where(function (Builder $query) {
                $query
                    ->terpakai()
                    ->orWhereNotNull('connected_titik_id')
                    ->orWhereHas('pelanggan');
            })
            ->exists();
    }



    private function deletePortsIfSafe(): void
    {
        $this->ports()
            ->whereDoesntHave('pelanggan')
            ->delete();
    }


    

    private static function applyAutoRouteData(
        array $data
    ): array {

        if (empty($data['parent_id'])) {
            $data['jarak_udara_meter'] = null;
            $data['panjang_kabel_meter'] = null;
            $data['jenis_jalur'] = null;

            return $data;
        }


        $parent = static::query()->find(
            $data['parent_id']
        );

        if (
            !$parent
            || $parent->lat === null
            || $parent->lng === null
        ) {
            $data['jarak_udara_meter'] = null;

            $data['panjang_kabel_meter'] =
                $data['panjang_kabel_meter'] ?? null;

            $data['jenis_jalur'] = null;

            return $data;
        }


        $jarakMeter = static::calculateDistanceMeter(
            (float) $parent->lat,
            (float) $parent->lng,
            (float) $data['lat'],
            (float) $data['lng']
        );

        $data['jarak_udara_meter'] = $jarakMeter;


        if (
            !isset($data['panjang_kabel_meter'])
            || $data['panjang_kabel_meter'] === null
            || $data['panjang_kabel_meter'] === ''
        ) {
            $data['panjang_kabel_meter'] =
                $jarakMeter;
        }


        $data['jenis_jalur'] =
            static::automaticRouteType(
                $parent->jenis,
                $data['jenis']
            );

        return $data;
    }

    /**
     * Menghitung jarak dua koordinat menggunakan rumus Haversine.
     */
    private static function calculateDistanceMeter(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $earthRadius = 6371000;

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);

        $deltaLat = deg2rad(
            $lat2 - $lat1
        );

        $deltaLng = deg2rad(
            $lng2 - $lng1
        );

        $a =
            sin($deltaLat / 2) ** 2
            +
            cos($lat1Rad)
            * cos($lat2Rad)
            * sin($deltaLng / 2) ** 2;

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        return round(
            $earthRadius * $c,
            2
        );
    }


    private static function automaticRouteType(
        ?string $parentType,
        ?string $childType
    ): string {
        if (
            $parentType === self::JENIS_OLT
            && $childType === self::JENIS_ODC
        ) {
            return 'Feeder';
        }

        if (
            $parentType === self::JENIS_ODC
            && $childType === self::JENIS_ODP
        ) {
            return 'Distribusi';
        }

        return 'Jalur';
    }
}
