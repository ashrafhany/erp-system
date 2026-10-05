<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\WebAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WebAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WebAccessSeeder::class);
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/employees')->assertRedirect('/login');
    }

    public function test_view_only_role_cannot_create_or_delete_employees(): void
    {
        $role = Role::create(['name' => 'employee-reader', 'guard_name' => 'admin']);
        $role->givePermissionTo('employees.view');
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)->get('/employees')->assertOk();
        $this->actingAs($user)->get('/employees/create')->assertForbidden();
        $this->actingAs($user)->post('/employees', [])->assertForbidden();
    }

    public function test_administrator_can_create_user_with_account_type_and_primary_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $role = Role::create(['name' => 'staff', 'guard_name' => 'admin']);

        $this->actingAs($admin)->post('/users', [
            'name' => 'Test Staff',
            'email' => 'staff@example.test',
            'account_type' => 'employee',
            'password' => 'abc123',
            'password_confirmation' => 'abc123',
            'role_id' => $role->id,
        ])->assertRedirect('/users');

        $user = User::where('email', 'staff@example.test')->firstOrFail();
        $this->assertSame('employee', $user->account_type);
        $this->assertSame('staff', $user->roles_name);
        $this->assertTrue($user->hasRole('staff'));
    }

    public function test_user_password_shorter_than_six_is_rejected(): void
    {
        $admin = User::findOrFail(1);

        $this->actingAs($admin)->post('/users', [
            'name' => 'Short Password',
            'email' => 'short@example.test',
            'account_type' => 'employee',
            'password' => 'abc12',
            'password_confirmation' => 'abc12',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'short@example.test']);
    }

    public function test_role_permissions_are_managed_in_controller_and_primary_name_tracks_rename(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $role = Role::create(['name' => 'staff', 'guard_name' => 'admin']);
        $staff = User::factory()->create(['roles_name' => 'staff']);
        $staff->assignRole($role);

        $this->actingAs($admin)->get('/roles')->assertOk();
        $this->actingAs($admin)->put("/roles/{$role->id}", [
            'name' => 'office-staff',
            'permissions' => ['employees.view'],
        ])->assertRedirect('/roles');

        $this->assertSame('office-staff', $staff->fresh()->roles_name);
        $this->assertTrue($staff->fresh()->can('employees.view'));
    }

    public function test_login_sends_user_to_first_allowed_section(): void
    {
        $role = Role::create(['name' => 'employee-reader', 'guard_name' => 'admin']);
        $role->givePermissionTo('employees.view');
        $user = User::factory()->create(['password' => 'StrongPassword123!']);
        $user->assignRole($role);

        $this->post('/login', ['email' => $user->email, 'password' => 'StrongPassword123!'])
            ->assertRedirect('/employees');
    }

    public function test_seeded_super_admin_has_fixed_ids_admin_guard_and_cannot_be_deleted(): void
    {
        $admin = User::findOrFail(1);
        $role = Role::findOrFail(1);

        $this->assertSame('admin@admin.com', $admin->email);
        $this->assertTrue(Hash::check('admin', $admin->password));
        $this->assertSame('super-admin', $admin->roles_name);
        $this->assertSame('super-admin', $role->name);
        $this->assertSame('admin', $role->guard_name);

        $secondAdmin = User::factory()->create();
        $secondAdmin->assignRole($role);
        $this->actingAs($secondAdmin)->delete('/users/1')->assertForbidden();
        $this->actingAs($secondAdmin)->delete('/roles/1')->assertForbidden();
        $this->actingAs($secondAdmin)->get('/roles/create')->assertOk()
            ->assertSee('toggle-all-permissions')
            ->assertSee('رجوع');
        $this->actingAs($secondAdmin)->get('/roles/1/edit')->assertOk()
            ->assertSee('toggle-all-permissions')
            ->assertSee('رجوع');
        $this->actingAs($secondAdmin)->put('/roles/1', [
            'name' => 'super-admin',
            'permissions' => ['roles.view', 'roles.update', 'roles.delete'],
        ])->assertRedirect('/roles');

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('super admin'));
        $this->assertFalse($role->hasPermissionTo('employees.view'));
        $this->seed(WebAccessSeeder::class);
        $this->assertFalse($role->fresh()->hasPermissionTo('employees.view'));
    }
}
