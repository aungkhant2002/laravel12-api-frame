<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60]; // seconds

    public function __construct(
        protected string $phone,
        protected string $message
    ) {}

    public function handle(): void
    {
        $token = config('services.sms.token');
        $sender = config('services.sms.sender');
        $baseUrl = rtrim(config('services.sms.base_url'), '/');

        if (!$token) {
            throw new \RuntimeException("SMSPOH_TOKEN is not configured");
        }

        $payload = [
            "to" => $this->phone,
            "message" => $this->message,
            "sender" => $sender,
        ];

        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->post("{$baseUrl}/send", $payload);

        if (!$response->successful()) {
            Log::error("SMS send failed", [
                "phone" => $this->phone,
                "status" => $response->status(),
                "body" => $response->body(),
            ]);

            // Throwing makes the job retry automatically
            throw new \RuntimeException("SMS send failed with status {$response->status()}");
        }

        // Optional: log success / store provider message id if they return it
        Log::info("SMS sent", ["phone" => $this->phone]);
    }
}
