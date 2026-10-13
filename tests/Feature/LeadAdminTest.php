<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Filament\Resources\Leads\RelationManagers\ActivitiesRelationManager;
use App\Filament\Widgets\EnquiriesNeedingAction;
use App\Filament\Widgets\EnquiryStats;
use App\Models\Lead;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_enquiries_are_private_to_admins(): void
    {
        $lead = Lead::factory()->create();

        $this->get("/admin/enquiries/{$lead->id}")->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create())->get('/admin/enquiries')->assertForbidden();

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin/enquiries')->assertOk()->assertSee($lead->name);
        $this->actingAs($admin)->get("/admin/enquiries/{$lead->id}")->assertOk()->assertSee($lead->reference);
        $this->actingAs($admin)->get('/admin/enquiries/create')->assertNotFound();
    }

    public function test_status_assignment_and_follow_up_changes_are_logged(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'name' => 'Latifah']);
        $this->actingAs($admin);
        $lead = Lead::factory()->create();

        Livewire::test(ViewLead::class, ['record' => $lead->id])
            ->callAction('assignToMe')
            ->callAction('updateFollowUp', [
                'status' => LeadStatus::Contacted->value,
                'assigned_to' => $admin->id,
                'next_action' => 'Call to agree a consultation time',
                'follow_up_at' => now()->addDays(2)->format('Y-m-d H:i'),
            ])
            ->assertHasNoActionErrors();

        $lead->refresh();
        $this->assertSame(LeadStatus::Contacted, $lead->status);
        $this->assertTrue($lead->assignee?->is($admin));

        $history = $lead->activities()->pluck('body', 'type');
        $this->assertSame('Assigned to Latifah.', $history['assignment']);
        $this->assertSame('Status changed from New to Contacted.', $history['status']);
        $this->assertStringContainsString('Call to agree a consultation time', $history['follow_up']);
        $this->assertTrue($lead->activities()->where('type', 'status')->sole()->user?->is($admin));
    }

    public function test_staff_can_add_private_notes(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $lead = Lead::factory()->create();

        Livewire::test(ActivitiesRelationManager::class, ['ownerRecord' => $lead, 'pageClass' => ViewLead::class])
            ->callAction(TestAction::make('addNote')->table(), ['body' => 'Asked for last year’s statements.'])
            ->assertHasNoActionErrors();

        $note = $lead->activities()->where('type', 'note')->sole();
        $this->assertSame('Asked for last year’s statements.', $note->body);
        $this->assertTrue($note->user?->is($admin));
    }

    public function test_spam_can_be_closed_in_bulk_and_tabs_filter_by_state(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $spam = Lead::factory()->count(2)->create();
        $overdue = Lead::factory()->status(LeadStatus::Contacted)->create(['follow_up_at' => now()->subDay()]);
        $won = Lead::factory()->status(LeadStatus::Won)->create();

        Livewire::test(ListLeads::class)
            ->assertCanSeeTableRecords([...$spam, $overdue])
            ->assertCanNotSeeTableRecords([$won])
            ->selectTableRecords($spam->modelKeys())
            ->callAction(TestAction::make('markClosed')->table()->bulk());

        $this->assertSame(
            [LeadStatus::Closed, LeadStatus::Closed],
            $spam->map(fn (Lead $lead): LeadStatus => $lead->refresh()->status)->all(),
        );

        Livewire::test(ListLeads::class)
            ->set('activeTab', 'overdue')
            ->assertCanSeeTableRecords([$overdue])
            ->assertCanNotSeeTableRecords([...$spam, $won]);
    }

    public function test_dashboard_counts_enquiries_needing_action(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        Lead::factory()->count(2)->create();
        Lead::factory()->status(LeadStatus::Contacted)->create(['follow_up_at' => now()->subHour()]);
        Lead::factory()->status(LeadStatus::Lost)->create(['follow_up_at' => now()->subHour()]);

        $new = Lead::query()->where('status', LeadStatus::New)->get();
        $overdue = Lead::query()->where('status', LeadStatus::Contacted)->sole();
        $lost = Lead::query()->where('status', LeadStatus::Lost)->sole();

        Livewire::test(EnquiryStats::class)
            ->assertSeeInOrder(['New', '2'])
            ->assertSeeInOrder(['Overdue follow-ups', '1']);

        Livewire::test(EnquiriesNeedingAction::class)
            ->assertCanSeeTableRecords([...$new, $overdue])
            ->assertCanNotSeeTableRecords([$lost]);
    }
}
