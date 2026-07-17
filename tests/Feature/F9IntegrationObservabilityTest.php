<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\IntegrationOutbox;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class F9IntegrationObservabilityTest extends TestCase
{
    public function test_appbill_mock_is_encrypted_idempotent_and_audited(): void
    {
        $company = Company::query()->active()->firstOrFail();
        $user = User::query()->where('is_developer', true)->firstOrFail();

        $response = $this
            ->actingAs($user)
            ->withSession([
                'company_id' => $company->id,
                'company_name' => $company->name,
            ])
            ->post(route('settings.integrations.test'));

        $response->assertRedirect();

        $event = IntegrationOutbox::query()->latest('id')->firstOrFail();
        $this->assertSame('sent', $event->status);
        $this->assertSame(202, $event->response_status);
        $this->assertSame(1, $event->attempts);

        $rawPayload = (string) DB::table('integration_outbox')->where('id', $event->id)->value('payload');
        $this->assertStringNotContainsString('requested_by', $rawPayload);
        $this->assertStringNotContainsString('system.connection.test', $rawPayload);

        $audit = AuditLog::query()->where('route_name', 'settings.integrations.test')->latest('id')->firstOrFail();
        $this->assertSame($user->id, $audit->user_id);
        $this->assertSame($company->id, $audit->company_id);
        $this->assertSame(302, $audit->response_status);
        $this->assertNotEmpty($audit->request_id);
    }

    public function test_web_and_api_login_are_rate_limited_after_five_attempts(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('login.proses'), [
                'email' => 'rate-limit-web-test',
                'password' => 'definitely-wrong',
            ])->assertStatus(302);
        }

        $this->post(route('login.proses'), [
            'email' => 'rate-limit-web-test',
            'password' => 'definitely-wrong',
        ])->assertStatus(429);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/v1/auth/login', [
                'identity' => 'rate-limit-api-test',
                'password' => 'definitely-wrong',
            ])->assertStatus(422);
        }

        $this->postJson('/api/v1/auth/login', [
            'identity' => 'rate-limit-api-test',
            'password' => 'definitely-wrong',
        ])->assertStatus(429);
    }
}
