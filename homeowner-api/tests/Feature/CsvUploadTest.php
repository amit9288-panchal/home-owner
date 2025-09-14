<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CsvUploadTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = config('services.api.token');
    }

    public function test_csv_upload_parses_names_and_stores_people()
    {
        Storage::fake('local');

        $csv = <<<CSV
name
Mr John Smith
Mrs Jane Smith
Mr & Mrs Bloggs
Dr P. Gunn
CSV;

        $file = UploadedFile::fake()->createWithContent('people.csv', $csv);

        $response = $this->postJson('/api/v1/upload', [
            'csv' => $file,
        ], [
            'X-Auth-Token' => $this->token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'errorCode' => null,
                'message' => 'CSV processed successfully',
                'data' => [],
            ]);

        $this->assertDatabaseHas('people', ['title' => 'Mr', 'first_name' => 'John', 'last_name' => 'Smith']);
        $this->assertDatabaseHas('people', ['title' => 'Mrs', 'first_name' => 'Jane', 'last_name' => 'Smith']);
        $this->assertDatabaseHas('people', ['title' => 'Mr', 'first_name' => null, 'last_name' => 'Bloggs']);
        $this->assertDatabaseHas('people', ['title' => 'Mrs', 'first_name' => null, 'last_name' => 'Bloggs']);
        $this->assertDatabaseHas('people', ['title' => 'Dr', 'initial' => 'P', 'last_name' => 'Gunn']);
    }

    public function test_upload_fails_without_token()
    {
        $csv = <<<CSV
name
Mr John Smith
CSV;

        $file = UploadedFile::fake()->createWithContent('people.csv', $csv);

        $response = $this->postJson('/api/v1/upload', [
            'csv' => $file,
        ]);

        $response->assertStatus(401);
    }
}
