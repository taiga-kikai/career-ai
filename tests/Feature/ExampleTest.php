<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** HTTPリクエストを通してアプリの応答を確認する最小例。 */
class ExampleTest extends TestCase
{
    /**
     * ログイン画面URLがHTTP 200を返すことを確認する。
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
