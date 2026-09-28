<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Pelanggan extends Model
{

    protected $table = 'pelanggan';

    protected $fillable = [
        'port_id',
        'nama',
        'no_layanan',
        'alamat',
        'lat',
        'lng',
        'keterangan',
    ];

    protected $casts = [
        'port_id' => 'int',
        'lat' => 'float',
        'lng' => 'float',
    ];

    //port odp ke pelanggan
    public function port(): BelongsTo
    {
        return $this->belongsTo(
            Port::class,
            'port_id'
        );
    }


    public function getOdpAttribute(): ?TitikJaringan
    {
        return $this->port?->titikJaringan;
    }


    public static function createWithPort(array $data): self
    {
        return DB::transaction(function () use ($data) {

            self::ensureValidOdp(
                (int) $data['odp_id']
            );

            $port = Port::findAvailableForPelanggan(
                (int) $data['port_id'],
                (int) $data['odp_id']
            );

            if (!$port) {
                throw ValidationException::withMessages([
                    'port_id' =>
                    'Port OUT ODP tidak valid atau sudah terpakai.',
                ]);
            }

            $pelanggan = self::create(
                self::customerAttributes(
                    $data,
                    $port->id
                )
            );

            $port->markUsedByPelanggan($pelanggan);

            return $pelanggan;
        });
    }


    public function updateWithPort(array $data): self
    {
        return DB::transaction(function () use ($data) {

            self::ensureValidOdp(
                (int) $data['odp_id']
            );

            $newPort = null;


            if (!empty($data['port_id'])) {
                $newPort = Port::findAvailableForPelanggan(
                    (int) $data['port_id'],
                    (int) $data['odp_id'],
                    $this->id
                );

                if (!$newPort) {
                    throw ValidationException::withMessages([
                        'port_id' =>
                        'Port OUT ODP tidak valid atau sudah terpakai.',
                    ]);
                }
            } else {

                $currentPort = Port::query()
                    ->lockForUpdate()
                    ->find($this->port_id);

                if (!$currentPort) {
                    throw ValidationException::withMessages([
                        'port_id' =>
                        'Pelanggan ini belum memiliki port. Pilih Port OUT ODP.',
                    ]);
                }


                if (
                    (int) $currentPort->titik_jaringan_id
                    !==
                    (int) $data['odp_id']
                ) {
                    throw ValidationException::withMessages([
                        'port_id' =>
                        'Jika mengganti ODP, pilih juga Port OUT ODP yang baru.',
                    ]);
                }
            }


            $selectedPortId = $newPort?->id
                ?? (int) $this->port_id;


            if (
                $newPort
                && (int) $this->port_id !== (int) $newPort->id
            ) {
                $this->releaseCurrentPort(
                    $newPort->id
                );
            }


            $this->update(
                self::customerAttributes(
                    $data,
                    $selectedPortId
                )
            );


            if ($newPort) {
                $newPort->markUsedByPelanggan($this);
            }

            return $this->refresh();
        });
    }


    public function deleteWithPort(): void
    {
        DB::transaction(function () {

            $port = Port::query()
                ->lockForUpdate()
                ->find($this->port_id);

            $this->delete();

            $port?->releaseConnection();
        });
    }


    private static function ensureValidOdp(
        int $odpId
    ): TitikJaringan {
        $odp = TitikJaringan::query()
            ->whereKey($odpId)
            ->where('jenis', 'ODP')
            ->first();

        if (!$odp) {
            throw ValidationException::withMessages([
                'odp_id' =>
                'Pelanggan harus terhubung ke titik jenis ODP.',
            ]);
        }

        return $odp;
    }


    private static function customerAttributes(
        array $data,
        int $portId
    ): array {
        return [
            'port_id' => $portId,
            'nama' => $data['nama'],
            'no_layanan' => $data['no_layanan'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'lat' => $data['lat'] ?? null,
            'lng' => $data['lng'] ?? null,
            'keterangan' => $data['keterangan'] ?? null,
        ];
    }


    private function releaseCurrentPort(
        ?int $exceptPortId = null
    ): void {

        if (!$this->port_id) {
            return;
        }

        if (
            $exceptPortId !== null
            && (int) $this->port_id === $exceptPortId
        ) {
            return;
        }


        $oldPort = Port::query()
            ->lockForUpdate()
            ->find($this->port_id);

        $oldPort?->releaseConnection();
    }



    public function toMapPayload(): array
    {

        $odp = $this->port?->titikJaringan;

        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'no_layanan' => $this->no_layanan,
            'alamat' => $this->alamat,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'keterangan' => $this->keterangan,

            'port' => $this->port
                ? [
                    'id' => $this->port->id,
                    'jenis_port' => $this->port->jenis_port,
                    'nomor_port' => $this->port->nomor_port,
                    'status' => $this->port->status,
                ]
                : null,

            'odp' => $odp
                ? [
                    'id' => $odp->id,
                    'jenis' => $odp->jenis,
                    'nama' => $odp->nama,
                    'lat' => $odp->lat,
                    'lng' => $odp->lng,
                    'rasio_splitter' => $odp->rasio_splitter,
                ]
                : null,
        ];
    }
}
