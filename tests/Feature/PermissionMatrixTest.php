<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckPermission;
use App\Models\UserAccount;
use App\Support\PermissionMatrix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PermissionMatrixTest extends TestCase
{
    public function test_permission_lookup_uses_the_users_role_id_from_the_database_matrix(): void
    {
        $roleId = 42;
        $user = new UserAccount;
        $user->id_role = $roleId;
        $matrix = new class(['manage_users' => ['admin' => 'full_access', (string) $roleId => 'full_access']]) extends PermissionMatrix
        {
            public function __construct(private array $matrix) {}

            public function activeMatrix(): array
            {
                return $this->matrix;
            }
        };
        $this->assertSame($roleId, $user->id_role);
        $this->assertTrue($matrix->allows($user, 'manage_users'));
        $this->app->instance(PermissionMatrix::class, $matrix);
        Auth::shouldReceive('user')->once()->andReturn($user);

        $response = (new CheckPermission)->handle(
            Request::create('/api/admin/users', 'GET'),
            fn () => response()->json(['allowed' => true]),
            'manage_users'
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_role_name_keys_do_not_grant_permissions(): void
    {
        $user = new UserAccount;
        $user->id_role = 42;
        $matrix = new class extends PermissionMatrix
        {
            public function activeMatrix(): array
            {
                return ['manage_users' => ['admin' => 'full_access']];
            }
        };
        $this->app->instance(PermissionMatrix::class, $matrix);
        Auth::shouldReceive('user')->once()->andReturn($user);

        $response = (new CheckPermission)->handle(
            Request::create('/api/admin/users', 'GET'),
            fn () => response()->json(['allowed' => true]),
            'manage_users'
        );

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_missing_permission_matrix_denies_access(): void
    {
        $user = new UserAccount;
        $user->id_role = 42;
        $matrix = new class extends PermissionMatrix
        {
            public function activeMatrix(): array
            {
                return [];
            }
        };
        $this->app->instance(PermissionMatrix::class, $matrix);
        Auth::shouldReceive('user')->once()->andReturn($user);

        $response = (new CheckPermission)->handle(
            Request::create('/api/admin/users', 'GET'),
            fn () => response()->json(['allowed' => true]),
            'manage_users'
        );

        $this->assertSame(403, $response->getStatusCode());
    }
}
