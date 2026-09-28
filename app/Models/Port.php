<?php

namespace App\Models;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;

class Port extends Model
{

    public const JENIS_IN = 'in';
    public const JENIS_OUT = 'out';

    public const STATUS_KOSONG = 'kosong';
    public const STATUS_TERPAKAI = 'terpakai';


    protected $fillable = [
        'titik_jaringan_id',
        'jenis_port',
        'nomor_port',
        'status',
        'redaman_manual',
        'sumber_port',
        'keterangan_redaman',
        'connected_titik_id',
    ];

    protected $casts = [
        'titik_jaringan_id' => 'integer',
        'nomor_port' => 'integer',
        'redaman_manual' => 'float',
        'connected_titik_id' => 'integer',
    ];


    public function titikJaringan(): BelongsTo
    {
        return $this->belongsTo(
            TitikJaringan::class,
            'titik_jaringan_id'
        );
    }

    //odc ke odp
    public function connectedTitik(): BelongsTo
    {
        return $this->belongsTo(
            TitikJaringan::class,
            'connected_titik_id'
        );
    }

    //odp ke pelanggan
    public function pelanggan(): HasOne
    {
        return $this->hasOne(
            Pelanggan::class,
            'port_id'
        );
    }


    public function scopeIn(Builder $query): Builder
    {
        return $query->where(
            'jenis_port',
            self::JENIS_IN
        );
    }

    public function scopeOut(Builder $query): Builder
    {
        return $query->where(
            'jenis_port',
            self::JENIS_OUT
        );
    }

    public function scopeKosong(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_KOSONG
        );
    }

    public function scopeTerpakai(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_TERPAKAI
        );
    }

    public function scopeMilikTitik(
        Builder $query,
        int $titikId
    ): Builder {
        return $query->where(
            'titik_jaringan_id',
            $titikId
        );
    }

    public function scopeMilikOdc(Builder $query): Builder
    {
        return $query->whereHas(
            'titikJaringan',
            function (Builder $subQuery) {
                $subQuery->where('jenis', 'ODC');
            }
        );
    }

    public function scopeMilikOdp(Builder $query): Builder
    {
        return $query->whereHas(
            'titikJaringan',
            function (Builder $subQuery) {
                $subQuery->where('jenis', 'ODP');
            }
        );
    }


    public static function findAvailableForPelanggan(
        int $portId,
        int $odpId,
        ?int $currentPelangganId = null
    ): ?self {
        return self::query()
            ->with([
                'titikJaringan',
                'pelanggan',
            ])
            ->whereKey($portId)
            ->milikTitik($odpId)
            ->out()
            ->milikOdp()
            ->whereNull('connected_titik_id')
            ->where(function (Builder $query) use ($currentPelangganId) {
                $query->kosong();

                if ($currentPelangganId !== null) {
                    $query->orWhereHas(
                        'pelanggan',
                        function (Builder $subQuery) use ($currentPelangganId) {
                            $subQuery->whereKey($currentPelangganId);
                        }
                    );
                }
            })
            ->lockForUpdate()
            ->first();
    }


    public static function findAvailableForOdp(
        int $portId,
        int $odcId,
        ?int $currentOdpId = null
    ): ?self {
        return self::query()
            ->with('titikJaringan')
            ->whereKey($portId)
            ->milikTitik($odcId)
            ->out()
            ->milikOdc()
            ->where(function (Builder $query) use ($currentOdpId) {
                $query->kosong();

                if ($currentOdpId !== null) {
                    $query->orWhere(
                        function (Builder $subQuery) use ($currentOdpId) {
                            $subQuery
                                ->terpakai()
                                ->where(
                                    'connected_titik_id',
                                    $currentOdpId
                                );
                        }
                    );
                }
            })
            ->lockForUpdate()
            ->first();
    }


    public function markUsedByPelanggan(
        Pelanggan $pelanggan
    ): void {
        $this->update([
            'status' => self::STATUS_TERPAKAI,
            'connected_titik_id' => null, //odc ke odp
            
            'keterangan_redaman' => $this->keterangan_redaman
                ?: 'Terhubung ke pelanggan ' . $pelanggan->nama,
        ]);
    }

    public function markUsedByTitik(
        TitikJaringan $titik
    ): void {
        $this->update([
            'status' => self::STATUS_TERPAKAI,
            'connected_titik_id' => $titik->id,

            'keterangan_redaman' => $this->keterangan_redaman
                ?: 'Terhubung ke ' . $titik->nama,
        ]);
    }


    public function releaseConnection(): void
    {
        $this->update([
            'status' => self::STATUS_KOSONG,
            'connected_titik_id' => null,
        ]);
    }


    public function updateRedamanByRole(
        array $data,
        ?string $role
    ): void {
        if (!in_array(
            $role,
            ['superadmin', 'admin', 'teknisi'],
            true
        )) {
            throw new AuthorizationException(
                'Anda tidak diizinkan memperbarui redaman port.'
            );
        }


        $allowedData = Arr::only($data, [
            'redaman_manual',
            'keterangan_redaman',
        ]);


        if (in_array(
            $role,
            ['superadmin', 'admin'],
            true
        )) {
            $allowedData['sumber_port'] =
                $data['sumber_port'] ?? null;
        }

        $this->update($allowedData);
    }



    public function getLabelPortAttribute(): string
    {
        if ($this->jenis_port === self::JENIS_IN) {
            return 'Port IN';
        }

        return 'OUT ' . $this->nomor_port;
    }


    public function getNamaStatusAttribute(): string
    {
        return $this->status === self::STATUS_TERPAKAI
            ? 'Terpakai'
            : 'Kosong';
    }

    public function getRedamanDisplayAttribute(): string
    {
        if ($this->redaman_manual === null) {
            return '-';
        }

        return number_format(
            $this->redaman_manual,
            2
        ) . ' dB';
    }
}
