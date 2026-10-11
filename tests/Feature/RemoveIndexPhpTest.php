<?php

namespace Tests\Feature;

use Tests\TestCase;

class RemoveIndexPhpTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_it_redirects_index_php_to_clean_root(): void
    {
        $response = $this->call('GET', '/index.php', server: [
            'REQUEST_URI' => '/index.php',
        ]);

        $response->assertStatus(301);
        $response->assertRedirect('/');
    }

    public function test_it_redirects_index_php_with_query_params_and_path(): void
    {
        $response = $this->call('GET', '/index.php/search?category=nintendo', server: [
            'REQUEST_URI' => '/index.php/search?category=nintendo',
        ]);

        $response->assertStatus(301);
        $response->assertRedirect('/search?category=nintendo');
    }

    public function test_clean_urls_are_not_redirected(): void
    {
        $response = $this->call('GET', '/categories', server: [
            'REQUEST_URI' => '/categories',
        ]);

        $response->assertStatus(200);
    }
}
