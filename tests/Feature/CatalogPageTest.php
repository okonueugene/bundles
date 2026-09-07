<?php

namespace Tests\Feature;

use Tests\TestCase;

class CatalogPageTest extends TestCase
{
    public function test_homepage_loads(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Your bundles')
            ->assertSee('Check Order')
            ->assertDontSee('Cart');
    }

    public function test_track_order_page_loads(): void
    {
        $this->get('/track-order')
            ->assertOk()
            ->assertSee('Order reference');
    }
}
