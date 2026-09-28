<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function index()
    {

        $users = User::orderedByRole()->get();

        return view(
            'administrator.users.index',
            compact('users')
        );
    }


    public function store(Request $request)
    {

        $data = $request->validate(
            $this->storeRules()
        );


        User::createManagedAccount($data);

        return back()->with(
            'success',
            'User berhasil dibuat.'
        );
    }


    public function update(
        Request $request,
        User $user
    ) {

        $data = $request->validate(
            $this->updateRules($user)
        );


        $user->updateManagedAccount(
            $data,
            auth()->user()
        );

        return back()->with(
            'success',
            'User berhasil diperbarui.'
        );
    }


    public function destroy(User $user)
    {
        $actor = auth()->user();

        $reason = $user->deletionBlockReason($actor);

        if ($reason !== null) {
            return back()->with(
                'error',
                $reason
            );
        }

        $user->deleteSafely($actor);

        return back()->with(
            'success',
            'User berhasil dihapus.'
        );
    }

    private function storeRules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
            ],

            'username' => [
                'required',
                'string',
                'max:20',
                'unique:users,username',
            ],

            'email' => [
                'required',
                'email',
                'max:100',
                'unique:users,email',
            ],

            'role' => [
                'required',
                Rule::in([
                    User::ROLE_SUPERADMIN,
                    User::ROLE_ADMIN,
                    User::ROLE_TEKNISI,
                ]),
            ],

            'password' => [
                'required',
                'string',
                'min:6',
            ],
        ];
    }

    /**
     * Aturan validasi saat memperbarui user.
     */
    private function updateRules(
        User $user
    ): array {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
            ],

            'username' => [
                'required',
                'string',
                'max:20',

                Rule::unique(
                    'users',
                    'username'
                )->ignore($user->id),
            ],

            'email' => [
                'required',
                'email',
                'max:100',

                Rule::unique(
                    'users',
                    'email'
                )->ignore($user->id),
            ],

            'role' => [
                'required',
                Rule::in([
                    User::ROLE_SUPERADMIN,
                    User::ROLE_ADMIN,
                    User::ROLE_TEKNISI,
                ]),
            ],


            'password' => [
                'nullable',
                'string',
                'min:6',
            ],
        ];
    }
}
