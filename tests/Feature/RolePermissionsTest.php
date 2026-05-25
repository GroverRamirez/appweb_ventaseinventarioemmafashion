<?php

use App\Enums\TeamRole;
use App\Models\Membership;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function userWithRole(string $roleValue): User
{
    $user = User::factory()->create();

    $team = Team::factory()->create();

    $team->members()->attach($user->id, [
        'role' => $roleValue,
    ]);

    $user->switchTeam($team);

    return $user->fresh();
}

describe('Owner (Propietaria) permissions', function () {
    test('owner can access dashboard', function () {
        $owner = userWithRole(TeamRole::Owner->value);
        $team = $owner->currentTeam;

        $response = $this
            ->actingAs($owner)
            ->get(route('dashboard', $team));

        $response->assertOk();
    });

    test('owner can view products', function () {
        $owner = userWithRole(TeamRole::Owner->value);
        $team = $owner->currentTeam;

        $response = $this
            ->actingAs($owner)
            ->get(route('products.index', $team));

        $response->assertOk();
    });

    test('owner can manage products', function () {
        $owner = userWithRole(TeamRole::Owner->value);
        $team = $owner->currentTeam;

        $response = $this
            ->actingAs($owner)
            ->post(route('products.store', $team), [
                'name' => 'Test Product',
                'price' => 100,
            ]);

        $response->assertRedirect();
    });

    test('owner can view reports', function () {
        $owner = userWithRole(TeamRole::Owner->value);
        $team = $owner->currentTeam;

        $response = $this
            ->actingAs($owner)
            ->get(route('reports.index', $team));

        $response->assertOk();
    });

    test('owner can access team management', function () {
        $owner = userWithRole(TeamRole::Owner->value);
        $team = $owner->currentTeam;

        $response = $this
            ->actingAs($owner)
            ->get(route('teams.edit', $team));

        $response->assertOk();
    });
});

describe('Member (Vendedora) permissions', function () {
    test('member can access dashboard', function () {
        $member = userWithRole(TeamRole::Member->value);
        $team = $member->currentTeam;

        $response = $this
            ->actingAs($member)
            ->get(route('dashboard', $team));

        $response->assertOk();
    });

    test('member can view products', function () {
        $member = userWithRole(TeamRole::Member->value);
        $team = $member->currentTeam;

        $response = $this
            ->actingAs($member)
            ->get(route('products.index', $team));

        $response->assertOk();
    });

    test('member cannot manage products', function () {
        $member = userWithRole(TeamRole::Member->value);
        $team = $member->currentTeam;

        $response = $this
            ->actingAs($member)
            ->post(route('products.store', $team), [
                'name' => 'Test Product',
                'price' => 100,
            ]);

        $response->assertForbidden();
    });

    test('member cannot view reports', function () {
        $member = userWithRole(TeamRole::Member->value);
        $team = $member->currentTeam;

        $response = $this
            ->actingAs($member)
            ->get(route('reports.index', $team));

        $response->assertForbidden();
    });

    test('member cannot access team management', function () {
        $member = userWithRole(TeamRole::Member->value);
        $team = $member->currentTeam;

        $response = $this
            ->actingAs($member)
            ->get(route('teams.edit', $team));

        $response->assertForbidden();
    });
});

describe('Permission helper methods', function () {
    test('owner has all permissions via hasTeamPermission', function () {
        $owner = userWithRole(TeamRole::Owner->value);
        $team = $owner->currentTeam;

        expect($owner->hasTeamPermission($team, \App\Enums\TeamPermission::ManageProduct))->toBeTrue();
        expect($owner->hasTeamPermission($team, \App\Enums\TeamPermission::ViewReport))->toBeTrue();
        expect($owner->hasTeamPermission($team, \App\Enums\TeamPermission::AddMember))->toBeTrue();
    });

    test('member has limited permissions via hasTeamPermission', function () {
        $member = userWithRole(TeamRole::Member->value);
        $team = $member->currentTeam;

        expect($member->hasTeamPermission($team, \App\Enums\TeamPermission::ViewProduct))->toBeTrue();
        expect($member->hasTeamPermission($team, \App\Enums\TeamPermission::CreateSale))->toBeTrue();
        expect($member->hasTeamPermission($team, \App\Enums\TeamPermission::ManageProduct))->toBeFalse();
        expect($member->hasTeamPermission($team, \App\Enums\TeamPermission::ViewReport))->toBeFalse();
        expect($member->hasTeamPermission($team, \App\Enums\TeamPermission::AddMember))->toBeFalse();
    });

    test('owner role has higher level than member', function () {
        expect(TeamRole::Owner->level())->toBe(2);
        expect(TeamRole::Member->level())->toBe(1);
        expect(TeamRole::Owner->isAtLeast(TeamRole::Member))->toBeTrue();
        expect(TeamRole::Member->isAtLeast(TeamRole::Owner))->toBeFalse();
    });
});