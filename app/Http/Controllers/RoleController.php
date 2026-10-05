<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->requireCrudPermissions('roles');
    }

    public function index(): View
    {
        return view('roles.index', ['roles' => Role::where('guard_name', 'admin')->withCount('users')->orderBy('name')->get()]);
    }

    public function create(): View
    {
        $this->ensureRegisteredPermissions();
        return view('roles.form', ['role' => new Role(), 'groups' => config('web_permissions')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'admin']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'تم إضافة الدور.');
    }

    public function edit(Role $role): View
    {
        $this->guardRole($role);
        $this->ensureRegisteredPermissions();
        return view('roles.form', ['role' => $role->load('permissions'), 'groups' => config('web_permissions')]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->guardRole($role);
        $data = $this->validated($request, $role);
        DB::transaction(function () use ($role, $data) {
            $role->update(['name' => $data['name']]);
            $role->syncPermissions($data['permissions'] ?? []);
            $role->users()->get()->each(fn ($user) => $user->forceFill(['roles_name' => $role->name])->save());
        });

        return redirect()->route('roles.index')->with('success', 'تم تحديث الدور.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->guardRole($role);
        abort_if($role->id === 1 || $role->name === 'super-admin', 403, 'لا يمكن حذف دور المدير الأساسي.');
        abort_if($role->users()->exists(), 409, 'الدور مستخدم حاليًا.');
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'تم حذف الدور.');
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        $this->ensureRegisteredPermissions();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->where('guard_name', 'admin')->ignore($role?->id)],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'distinct', Rule::exists('permissions', 'name')->where('guard_name', 'admin')],
        ]);
        if ($role?->id === 1) {
            abort_if($data['name'] !== 'super-admin', 403, 'اسم دور المدير الأساسي ثابت.');
            $data['permissions'] = array_values(array_unique([...($data['permissions'] ?? []), 'super admin']));
        } else {
            abort_if(in_array($data['name'], ['super-admin', 'super admin'], true) || in_array('super admin', $data['permissions'] ?? [], true), 403);
        }
        return $data;
    }

    private function ensureRegisteredPermissions(): void
    {
        foreach (config('web_permissions') as $permissions) {
            foreach (array_keys($permissions) as $name) {
                Permission::findOrCreate($name, 'admin');
            }
        }
    }

    private function guardRole(Role $role): void
    {
        abort_if($role->guard_name !== 'admin', 403);
    }
}
