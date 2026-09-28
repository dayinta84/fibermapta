@extends('layouts.dashboard')

@section('title','Kelola User')
@section('topbar_title','Kelola User')

@section('content')

<style>
    .user-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .user-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        padding: 22px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06);
    }

    .user-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .user-title {
        margin: 0;
        font-size: 24px;
        line-height: 1.15;
        font-weight: 900;
        color: #020617;
    }

    .user-subtitle {
        margin-top: 6px;
        font-size: 14px;
        color: #64748b;
        line-height: 1.5;
    }

    .user-top-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .user-page .btn {
        min-height: 40px;
        border-radius: 13px;
        padding: 9px 14px;
        border: 1px solid transparent;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        transition: 0.16s ease;
        white-space: nowrap;
    }

    .user-page .btn:hover {
        transform: translateY(-1px);
    }

    .user-page .btn-primary {
        color: #ffffff;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        box-shadow: 0 10px 22px rgba(37, 99, 235, 0.20);
    }

    .user-page .btn-light {
        color: #0f172a;
        background: #ffffff;
        border-color: #e5e7eb;
    }

    .user-form-wrap {
        display: none;
        margin-bottom: 18px;
    }

    .user-form-wrap.open {
        display: block;
    }

    .user-form {
        padding: 16px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
    }

    .user-form-title {
        margin: 0 0 14px;
        font-size: 17px;
        font-weight: 900;
        color: #020617;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        align-items: start;
    }

    .field {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 15px;
        padding: 13px;
    }

    .field label {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 900;
        color: #334155;
    }

    .field input,
    .field select {
        width: 100%;
        height: 46px;
        padding: 0 13px;
        border-radius: 13px;
        border: 1px solid #dbe3ef;
        background: #ffffff;
        color: #0f172a;
        font-size: 14px;
        outline: none;
        transition: 0.16s ease;
    }

    .field input:focus,
    .field select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .field select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        padding-right: 42px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%2364758b' stroke-width='1.9' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 16px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 16px;
        flex-wrap: wrap;
    }

    .alert-success,
    .alert-error {
        margin-bottom: 14px;
        padding: 12px 14px;
        border-radius: 14px;
        font-size: 14px;
        line-height: 1.5;
    }

    .alert-success {
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .user-table-wrap {
        overflow: auto;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        background: #ffffff;
    }

    .user-table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
    }

    .user-table thead th {
        text-align: left;
        padding: 13px 14px;
        background: #f8fafc;
        color: #334155;
        font-size: 13px;
        font-weight: 900;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }

    .user-table tbody td {
        padding: 14px;
        font-size: 14px;
        border-bottom: 1px solid #eef2f7;
        color: #0f172a;
        vertical-align: middle;
    }

    .user-table tbody tr:hover td {
        background: #fafcff;
    }

    .badge-role {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 78px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        text-transform: capitalize;
    }

    .badge-role.superadmin {
        color: #6d28d9;
        background: #ede9fe;
    }

    .badge-role.admin {
        color: #1d4ed8;
        background: #dbeafe;
    }

    .badge-role.teknisi {
        color: #047857;
        background: #d1fae5;
    }

    .action-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .action-row form {
        margin: 0;
    }

    .icon-action-btn {
        width: 38px;
        height: 38px;
        border-radius: 13px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        font-weight: 900;
        cursor: pointer;
        text-decoration: none;
        transition: 0.16s ease;
        line-height: 1;
    }

    .icon-action-btn:hover {
        transform: translateY(-1px);
    }

    .icon-action-btn.edit {
        color: #1d4ed8;
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .icon-action-btn.edit:hover {
        background: #dbeafe;
    }

    .icon-action-btn.delete {
        color: #dc2626;
        background: #fee2e2;
        border-color: #fecaca;
    }

    .icon-action-btn.delete:hover {
        background: #fecaca;
    }

    .icon-action-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
        transform: none;
    }

    .icon-action-btn:disabled:hover {
        background: #fee2e2;
    }



    .user-self-note {
        margin-top: 7px;
        font-size: 12px;
        color: #64748b;
    }

    .empty {
        text-align: center;
        color: #64748b;
        padding: 24px !important;
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(4px);
    }

    .modal-backdrop.open {
        display: flex;
    }

    .modal-card {
        width: min(720px, 100%);
        max-height: 92vh;
        overflow-y: auto;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.25);
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-title {
        font-size: 20px;
        font-weight: 900;
        color: #020617;
    }

    .modal-close {
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 12px;
        background: #f1f5f9;
        color: #334155;
        cursor: pointer;
        font-weight: 900;
    }

    .modal-body {
        padding: 20px;
    }

    .edit-user-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .edit-user-grid .full {
        grid-column: 1 / -1;
    }

    .label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 12px;
        font-weight: 900;
    }

    .input {
        width: 100%;
        height: 46px;
        border: 1px solid #dbe3ef;
        border-radius: 13px;
        padding: 0 13px;
        background: #ffffff;
        color: #0f172a;
        font-size: 14px;
        outline: none;
    }

    .input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .role-hint {
        margin-top: 6px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.4;
        display: none;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 16px;
        flex-wrap: wrap;
    }

    @media (max-width: 1100px) {
        .form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .user-card {
            padding: 18px;
        }

        .form-grid,
        .edit-user-grid {
            grid-template-columns: 1fr;
        }

        .edit-user-grid .full {
            grid-column: auto;
        }

        .user-title {
            font-size: 22px;
        }

        .user-top-actions,
        .user-top-actions .btn,
        .form-actions .btn,
        .modal-actions .btn {
            width: 100%;
        }
    }
</style>

<div class="user-page">
    <div class="user-card">

        <div class="user-head">
            <div>
                <div class="user-title">Kelola User</div>
                <div class="user-subtitle">Hanya Superadmin yang bisa tambah, edit, dan hapus user.</div>
            </div>

            <div class="user-top-actions">
                <button type="button" class="btn btn-primary" id="btnToggleAddUser">Tambah User</button>
            </div>
        </div>

        @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
        <div class="alert-error">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="user-form-wrap {{ $errors->any() ? 'open' : '' }}" id="addUserFormWrap">
            <form class="user-form" method="POST" action="{{ route('users.store') }}">
                @csrf

                <div class="user-form-title">Tambah User Baru</div>

                <div class="form-grid">
                    <div class="field">
                        <label>Nama</label>
                        <input name="name" value="{{ old('name') }}" placeholder="Nama" required>
                    </div>

                    <div class="field">
                        <label>Username</label>
                        <input name="username" value="{{ old('username') }}" placeholder="Username" required>
                    </div>

                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
                    </div>

                    <div class="field">
                        <label>Role</label>
                        <select name="role" required>
                            <option value="">Pilih role</option>
                            <option value="superadmin" @selected(old('role')==='superadmin' )>Superadmin</option>
                            <option value="admin" @selected(old('role')==='admin' )>Admin</option>
                            <option value="teknisi" @selected(old('role')==='teknisi' )>Teknisi</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Min 6 karakter" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-light" id="btnCancelAddUser">Batal</button>
                    <button class="btn btn-primary" type="submit">Tambah User</button>
                </div>
            </form>
        </div>

        <div class="user-table-wrap">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th style="width:150px;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users->sortByDesc(fn($user) => auth()->id() === $user->id) as $u)
                    @php
                    $payload = [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                    'email' => $u->email,
                    'role' => $u->role,
                    'is_self' => auth()->id() === $u->id,
                    ];
                    @endphp

                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->username }}</td>
                        <td>{{ $u->email }}</td>
                        <td>
                            <span class="badge-role {{ $u->role }}">
                                {{ $u->role }}
                            </span>
                        </td>
                        <td>
                            <div class="action-row">
                                <button
                                    type="button"
                                    class="icon-action-btn edit"
                                    title="Edit user"
                                    data-user='@json($payload)'
                                    onclick="openEditModal(this)">
                                    ✎
                                </button>

                                @if(auth()->id() === $u->id)
                                <button
                                    type="button"
                                    class="icon-action-btn delete"
                                    disabled
                                    title="Akun yang sedang digunakan tidak bisa dihapus">
                                    ×
                                </button>
                                @else
                                <form
                                    method="POST"
                                    action="{{ route('users.destroy', $u->id) }}"
                                    onsubmit="return confirm('Hapus user ini?');">
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="icon-action-btn delete"
                                        title="Hapus user">
                                        ×
                                    </button>
                                </form>
                                @endif
                            </div>

                            @if(auth()->id() === $u->id)
                            <div class="user-self-note">(akun terpakai)</div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="empty">Belum ada user.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

<div class="modal-backdrop" id="editUserModal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-header">
            <div class="modal-title">Edit User</div>
            <button class="modal-close" type="button" onclick="closeEditModal()">✕</button>
        </div>

        <div class="modal-body">
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')

                <div class="edit-user-grid">
                    <div>
                        <label class="label">Nama</label>
                        <input class="input" id="edit_name" name="name" required>
                    </div>

                    <div>
                        <label class="label">Username</label>
                        <input class="input" id="edit_username" name="username" required>
                    </div>

                    <div class="full">
                        <label class="label">Email</label>
                        <input class="input" type="email" id="edit_email" name="email" required>
                    </div>

                    <div>
                        <label class="label">Role</label>
                        <select class="input" id="edit_role" name="role" required>
                            <option value="superadmin">Superadmin</option>
                            <option value="admin">Admin</option>
                            <option value="teknisi">Teknisi</option>
                        </select>

                        <div id="roleHint" class="role-hint">
                            Kamu sedang edit akun sendiri.
                        </div>
                    </div>

                    <div>
                        <label class="label">Password baru (opsional)</label>
                        <input class="input" type="password" id="edit_password" name="password" placeholder="Kosongkan jika tidak diganti">
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-light" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const modal = document.getElementById('editUserModal');
    const form = document.getElementById('editUserForm');

    const addUserFormWrap = document.getElementById('addUserFormWrap');
    const btnToggleAddUser = document.getElementById('btnToggleAddUser');
    const btnCancelAddUser = document.getElementById('btnCancelAddUser');

    function openEditModal(btn) {
        const raw = btn.getAttribute('data-user');
        if (!raw) return;

        const u = JSON.parse(raw);

        form.action = "{{ url('/users') }}/" + u.id;

        document.getElementById('edit_name').value = u.name ?? '';
        document.getElementById('edit_username').value = u.username ?? '';
        document.getElementById('edit_email').value = u.email ?? '';
        document.getElementById('edit_role').value = u.role ?? 'teknisi';
        document.getElementById('edit_password').value = '';

        const hint = document.getElementById('roleHint');

        if (u.is_self) {
            hint.style.display = 'block';
        } else {
            hint.style.display = 'none';
        }

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');

        setTimeout(() => document.getElementById('edit_name').focus(), 50);
    }

    function closeEditModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    }

    function openAddUserForm() {
        addUserFormWrap.classList.add('open');
        btnToggleAddUser.textContent = 'Tutup Form';
    }

    function closeAddUserForm() {
        addUserFormWrap.classList.remove('open');
        btnToggleAddUser.textContent = 'Tambah User';
    }

    function toggleAddUserForm() {
        if (addUserFormWrap.classList.contains('open')) {
            closeAddUserForm();
        } else {
            openAddUserForm();
        }
    }

    if (btnToggleAddUser) {
        btnToggleAddUser.addEventListener('click', toggleAddUserForm);
    }

    if (btnCancelAddUser) {
        btnCancelAddUser.addEventListener('click', closeAddUserForm);
    }

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeEditModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('open')) {
            closeEditModal();
        }
    });
</script>
@endpush