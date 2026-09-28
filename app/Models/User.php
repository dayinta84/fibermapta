<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_TEKNISI = 'teknisi';


    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function scopeOrderedByRole(
        Builder $query
    ): Builder {
        return $query
            ->orderByRaw("
                CASE role
                    WHEN 'superadmin' THEN 1
                    WHEN 'admin' THEN 2
                    WHEN 'teknisi' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('name');
    }


    public function isSuperadmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isTeknisi(): bool
    {
        return $this->role === self::ROLE_TEKNISI;
    }



    public static function createManagedAccount(
        array $data
    ): self {
        return static::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => $data['password'],
        ]);
    }


    
    public function updateManagedAccount(
        array $data,
        User $actor
    ): self {

        if (
            $actor->is($this)
            && $data['role'] !== self::ROLE_SUPERADMIN
        ) {
            throw ValidationException::withMessages([
                'role' =>
                'Role akun superadmin yang sedang digunakan tidak boleh diturunkan.',
            ]);
        }


        if (
            !isset($data['password'])
            || $data['password'] === null
            || $data['password'] === ''
        ) {
            unset($data['password']);
        }

        $this->update([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role'],

            'password' =>
            $data['password'] ?? $this->password,
        ]);

        return $this->refresh();
    }



    public function deletionBlockReason(
        User $actor
    ): ?string {

        if ($actor->is($this)) {
            return 'Tidak bisa menghapus akun yang sedang digunakan.';
        }

        if (
            $this->isSuperadmin()
            && static::query()
            ->where(
                'role',
                self::ROLE_SUPERADMIN
            )
            ->count() <= 1
        ) {
            return 'Minimal harus ada satu akun superadmin.';
        }

        return null;
    }


    public function deleteSafely(
        User $actor
    ): void {
        DB::transaction(function () use ($actor) {

            $user = static::query()
                ->lockForUpdate()
                ->findOrFail($this->id);

            $reason =
                $user->deletionBlockReason($actor);

            if ($reason !== null) {
                throw ValidationException::withMessages([
                    'user' => $reason,
                ]);
            }

            $user->delete();
        });
    }
}
