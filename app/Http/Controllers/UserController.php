<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->requireCrudPermissions('users');
    }

    public function index(): View
    {
        return view('users.index', ['users' => User::with('roles')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('users.form', [
            'user' => new User(),
            'roles' => $this->availableRoles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'account_type' => ['required', Rule::in(['admin', 'employee'])],
            'password' => ['required', 'confirmed', 'min:6'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
        ]);
        DB::transaction(function () use ($request, $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            $user->forceFill(['account_type' => $data['account_type']])->save();
            $this->assignRole($request, $user, $data['role_id'] ?? null);
        });

        return redirect()->route('users.index')->with('success', 'تم إضافة المستخدم.');
    }

    public function edit(User $user): View
    {
        $this->guardAdministrator($user);

        return view('users.form', [
            'user' => $user->load('roles'),
            'roles' => $this->availableRoles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->guardAdministrator($user);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'account_type' => ['required', Rule::in(['admin', 'employee'])],
            'password' => ['nullable', 'confirmed', 'min:6'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
        ]);
        abort_if($user->id === 1 && ($data['email'] !== 'admin@admin.com' || $data['account_type'] !== 'admin'), 403, 'هوية المدير الأساسي ثابتة.');

        DB::transaction(function () use ($request, $data, $user) {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                ...(! empty($data['password']) ? ['password' => Hash::make($data['password'])] : []),
            ]);
            $user->forceFill(['account_type' => $data['account_type']])->save();
            $this->assignRole($request, $user, $data['role_id'] ?? null);
        });

        return redirect()->route('users.index')->with('success', 'تم تحديث المستخدم.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 403, 'لا يمكنك حذف حسابك.');
        $this->guardAdministrator($user);
        abort_if($user->id === 1 || $user->hasRole('super-admin'), 403, 'لا يمكن حذف مدير النظام.');
        $user->delete();

        return redirect()->route('users.index')->with('success', 'تم حذف المستخدم.');
    }

    private function availableRoles()
    {
        return Role::where('guard_name', 'admin')
            ->when(! auth()->user()->hasRole('super-admin'), fn ($query) => $query->where('name', '!=', 'super-admin'))
            ->orderBy('name')->get();
    }

    private function assignRole(Request $request, User $user, ?int $id): void
    {
        if (! $request->user()->can('roles.update')) {
            return;
        }

        $role = $id ? Role::where('guard_name', 'admin')->findOrFail($id) : null;
        abort_if($role?->name === 'super-admin' && ! $request->user()->hasRole('super-admin'), 403);
        abort_if($user->id === 1 && $role?->name !== 'super-admin', 403, 'لا يمكنك إزالة دور المدير الأساسي.');
        abort_if($user->is($request->user()) && $user->hasRole('super-admin') && $role?->name !== 'super-admin', 403, 'لا يمكنك إزالة صلاحية المدير من حسابك.');
        $user->syncRoles($role ? [$role] : []);
        $user->forceFill(['roles_name' => $role?->name])->save();
    }

    private function guardAdministrator(User $user): void
    {
        abort_if($user->hasRole('super-admin') && ! auth()->user()->hasRole('super-admin'), 403);
    }
}
