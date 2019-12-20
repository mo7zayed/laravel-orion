<?php

namespace Orion\Tests\Feature;

use Illuminate\Foundation\Testing\TestResponse;
use Orion\Tests\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param TestResponse $response
     * @param int $currentPage
     * @param int $from
     * @param int $lastPage
     * @param int $perPage
     * @param int $to
     * @param int $total
     */
    protected function assertSuccessfulIndexResponse($response, $currentPage = 1, $from = 1, $lastPage = 1, $perPage = 15, $to = 3, $total = 3)
    {
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total']
        ]);
        $response->assertJson([
            'meta' => [
                'current_page' => $currentPage,
                'from' => $from,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'to' => $to,
                'total' => $total
            ]
        ]);
    }

    /**
     * @param TestResponse $response
     */
    protected function assertSuccessfulShowResponse($response)
    {
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    /**
     * @param TestResponse $response
     */
    protected function assertUnauthorizedResponse($response)
    {
        $response->assertStatus(403);
        $response->assertJson(['message' => 'This action is unauthorized.']);
    }
}