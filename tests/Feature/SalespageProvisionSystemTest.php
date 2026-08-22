<?php

namespace Tests\Feature;

use App\Models\Salespage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalespageProvisionSystemTest extends TestCase
{
    use RefreshDatabase;

    private function page(array $attrs = []): Salespage
    {
        return Salespage::factory()->for(User::factory(), 'user')->create($attrs);
    }

    private function payload(Salespage $page, array $extra): array
    {
        return array_merge(['title' => $page->title, 'price' => $page->price, 'gateway' => $page->gateway], $extra);
    }

    public function test_owner_can_link_a_salespage_to_a_system(): void
    {
        $page = $this->page();

        $this->actingAs($page->user)
            ->put(route('salespages.update', $page), $this->payload($page, ['provision_system' => 'statushadis']))
            ->assertRedirect();

        $this->assertSame('statushadis', $page->fresh()->provision_system);
    }

    public function test_an_unknown_system_is_rejected(): void
    {
        $page = $this->page(['provision_system' => 'statushadis']);

        $this->actingAs($page->user)
            ->put(route('salespages.update', $page), $this->payload($page, ['provision_system' => 'bukan-system']))
            ->assertSessionHasErrors('provision_system');

        $this->assertSame('statushadis', $page->fresh()->provision_system);
    }

    public function test_it_can_be_cleared_back_to_no_system(): void
    {
        $page = $this->page(['provision_system' => 'statushadis']);

        $this->actingAs($page->user)
            ->put(route('salespages.update', $page), $this->payload($page, ['provision_system' => '']))
            ->assertRedirect();

        $this->assertNull($page->fresh()->provision_system);
    }

    public function test_other_settings_forms_do_not_wipe_the_linked_system(): void
    {
        $page = $this->page(['provision_system' => 'statushadis']);

        // The marketing form never submits provision_system.
        $this->actingAs($page->user)
            ->put(route('salespages.update', $page), $this->payload($page, ['fb_pixel' => '1023456789012345']))
            ->assertRedirect();

        $this->assertSame('statushadis', $page->fresh()->provision_system);
    }
}
