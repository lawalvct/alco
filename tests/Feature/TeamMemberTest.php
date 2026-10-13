<?php

namespace Tests\Feature;

use App\Filament\Resources\TeamMembers\Pages\CreateTeamMember;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class TeamMemberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_about_page_lists_only_published_members_in_order(): void
    {
        TeamMember::factory()->create(['name' => 'Second Leader', 'sort_order' => 2, 'role' => 'Partner']);
        TeamMember::factory()->create(['name' => 'First Leader', 'sort_order' => 1]);
        TeamMember::factory()->create(['name' => 'Unpublished Person', 'is_published' => false]);

        $this->get('/about')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/Section')
            ->where('kind', 'about')
            ->has('team', 2)
            ->where('team.0.name', 'First Leader')
            ->where('team.0.role', null)
            ->where('team.1.name', 'Second Leader')
            ->where('team.1.role', 'Partner')
            ->missing('team.0.is_published')
            ->etc());
    }

    public function test_admin_can_add_a_team_member_at_the_end_of_the_list(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        TeamMember::factory()->create(['sort_order' => 4]);

        Livewire::test(CreateTeamMember::class)
            ->fillForm(['name' => 'Abdulazeez Azeez Olaitan', 'is_published' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $member = TeamMember::where('name', 'Abdulazeez Azeez Olaitan')->sole();
        $this->assertSame(5, $member->sort_order);
        $this->assertNull($member->role);
    }

    public function test_admin_can_open_every_cms_screen(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        foreach ([
            '/admin', '/admin/media', '/admin/media/create', '/admin/homepage-slider',
            '/admin/homepage-slider/create', '/admin/packages', '/admin/packages/create',
            '/admin/team', '/admin/team/create', '/admin/site-settings',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }
}
