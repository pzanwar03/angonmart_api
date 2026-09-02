<?php

namespace Marvel\Otp\Gateways;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Marvel\Database\Models\OtpVerification;
use Marvel\Otp\OtpInterface;
use Marvel\Otp\Result;

class SmsgatewaybdGateway implements OtpInterface
{
    private string $baseUrl;
    private string $clientId;
    private string $key;
    private ?string $senderId;
    private int $otpLength;
    private int $otpTtl;
    private string $otpMessage;

    private int $maxAttempts = 5;
    private int $resendCooldown = 60;

    public function __construct()
    {
        $this->baseUrl   = rtrim(config('services.smsgatewaybd.base_url', 'https://api.smsgateway.com.bd/api'), '/');
        $this->clientId  = config('services.smsgatewaybd.client_id', '');
        $this->key       = config('services.smsgatewaybd.key', '');
        $this->senderId  = config('services.smsgatewaybd.sender_id') ?: null;
        $this->otpLength = (int) config('services.smsgatewaybd.otp_length', 6);
        $this->otpTtl    = (int) config('services.smsgatewaybd.otp_ttl', 300);
        $this->otpMessage = config('services.smsgatewaybd.otp_message', 'Your verification code is :code');
    }

    /**
     * Generate an OTP, persist it, and deliver via smsgateway.com.bd /send-message.
     */
    public function startVerification($phone_number): Result
    {
        $recent = OtpVerification::where('phone_number', $phone_number)
            ->where('created_at', '>=', now()->subSeconds($this->resendCooldown))
            ->latest()
            ->first();

        if ($recent) {
            return new Result(['Please wait before requesting a new code.']);
        }

        $code = $this->generateCode();
        $uuid = Str::uuid()->toString();

        OtpVerification::create([
            'id'           => $uuid,
            'phone_number' => $phone_number,
            'code'         => Hash::make($code),
            'expires_at'   => now()->addSeconds($this->otpTtl),
            'attempts'     => 0,
        ]);

        $message = str_replace(':code', $code, $this->otpMessage);

        return $this->postSms($phone_number, $message)
            ? new Result($uuid)
            : new Result(['SMS delivery failed. Please try again.']);
    }

    /**
     * Validate the OTP submitted by the user.
     */
    public function checkVerification($id, $code, $phone_number): Result
    {
        $record = OtpVerification::find($id);

        if (!$record) {
            return new Result(['OTP not found. Please request a new code.']);
        }

        if ($record->phone_number !== $phone_number) {
            return new Result(['Phone number mismatch.']);
        }

        if ($record->verified_at !== null) {
            return new Result(['This code has already been used.']);
        }

        if (now()->isAfter($record->expires_at)) {
            return new Result(['OTP has expired. Please request a new code.']);
        }

        if ($record->attempts >= $this->maxAttempts) {
            return new Result(['Too many failed attempts. Please request a new code.']);
        }

        if (!Hash::check($code, $record->code)) {
            $record->increment('attempts');
            return new Result(['Invalid verification code.']);
        }

        $record->update(['verified_at' => now()]);

        return new Result($id);
    }

    /**
     * Send a plain SMS (used by SmsTrait for order / refund notifications).
     */
    public function sendSms($phone_number, $messageBody): Result
    {
        return $this->postSms($phone_number, $messageBody)
            ? new Result('sent')
            : new Result(['SMS delivery failed.']);
    }

    /**
     * POST the message to /send-message and return true on success.
     */
    private function postSms(string $phone_number, string $message): bool
    {
        try {
            $payload = [
                'client_id' => $this->clientId,
                'key'       => $this->key,
                'recipient' => $this->normalizePhone($phone_number),
                'message'   => $message,
            ];

            if ($this->senderId) {
                $payload['sender_id'] = $this->senderId;
            }

            $response = Http::timeout(15)
                ->asJson()
                ->post($this->baseUrl . '/send-message', $payload);

            $body = $response->json();

            return ($body['response_code'] ?? null) === 200;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Normalize any BD phone number to 01XXXXXXXXX (11 digits).
     *
     * Accepts: +8801..., 8801..., 01..., 1...
     */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^\d+]/', '', $phone);

        if (str_starts_with($phone, '+880')) {
            $phone = '0' . substr($phone, 4);
        } elseif (str_starts_with($phone, '880')) {
            $phone = '0' . substr($phone, 3);
        }

        if (!str_starts_with($phone, '0') && strlen($phone) === 10) {
            $phone = '0' . $phone;
        }

        return $phone;
    }

    private function generateCode(): string
    {
        $max = (int) str_repeat('9', $this->otpLength);
        $min = (int) ('1' . str_repeat('0', $this->otpLength - 1));
        return (string) random_int($min, $max);
    }
}
