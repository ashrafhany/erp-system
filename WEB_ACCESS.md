# Web access and permissions

The web interface uses Laravel sessions for login and `spatie/laravel-permission` for roles and permissions. Spatie uses the `admin` guard; the web session still uses Laravel's `web` guard.

## Setup

```bash
composer install
php artisan migrate
php artisan db:seed --class=WebAccessSeeder
```

The seeder creates user **ID 1** (`admin@admin.com`, password `admin`) and role **ID 1** (`super-admin`, guard `admin`). The password is stored as a hash. Seeding again keeps an existing changed password. Both records are protected from deletion through the dashboard. Change the initial password from the Users page after logging in at `/login`.

The `super-admin` role can be edited, but cannot be deleted or renamed. Its `admin` guard and reserved `super admin` permission remain fixed. The role form has a select-all button.

`getMenuData()` in `app/Support/menu.php` is the web CRUD registry. Add a resource key and its Font Awesome icon there to make its permissions appear in the role form. The same registry builds the sidebar; a user sees a registered section only with its `resource.view` permission. The route and controller for a new resource must also exist. Standard CRUD permissions are generated automatically for new keys; custom labels and actions go in `config/web_permissions.php`. Opening the role form creates any missing Spatie permission records, and running `WebAccessSeeder` also registers them. Existing role choices remain intact.

Each web section has separate `view`, `create`, `update`, and `delete` permissions. Check-in, check-out, payroll generation, approval and payment, invoice sending and recording payments have their own permissions. Permission middleware is registered in controller constructors; routes carry only login/session protection.

`users.account_type` is the account category (`admin` or `employee`) and does not grant access by itself. `users.roles_name` reflects the primary role; Spatie's tables remain the authorization source. The dashboard assigns one primary role per user.
