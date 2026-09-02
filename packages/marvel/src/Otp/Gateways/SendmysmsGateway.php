<?php

namespace Marvel\Otp\Gateways;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Marvel\Database\Models\OtpVerification;
use Marvel\Otp\OtpInterface;
use Marvel\Otp\Result;

class SendmysmsGateway implements OtpInterface
{
    private string $url;
    private string $user;
    private string $key;
    private int $otpLength;
    private int $otpTtl;
    private string $otpMessage;

    /** Maximum failed attempts before the code is invalidated. */
    private int $maxAttempts = 5;

    /** Minimum seconds between resend requests for the same number. */
    private int $resendCooldown = 60;

    public function __construct()
    {
        $this->url        = config('services.sendmysms.url', 'https://sendmysms.net/api.php');
        $this->user       = config('services.sendmysms.user', '');
        $this->key        = config('services.sendmysms.key', '');
        $this->otpLength  = (int) config('services.sendmysms.otp_length', 6);
        $this->otpTtl     = (int) config('services.sendmysms.otp_ttl', 300);
        $this->otpMessage = config('services.sendmysms.otp_message', 'Your verification code is :code');
    }

    /**
     * Generate a code, persist it, and send via sendmysms.net.
     */
    public function startVerification($phone_number): Result
    {
        $localPhone = $this->normalizePhone($phone_number);

        // Enforce resend cooldown.
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

        $url = $this->url
            . '?to='   . urlencode($localPhone)
            . '&msg='  . urlencode($message)
            . '&user=' . urlencode($this->user)
            . '&key='  . urlencode($this->key);

        $body = $this->curlGet($url);

        if ($body === null || ($body['status'] ?? '') !== 'OK') {
            $errorMsg = $body['response'] ?? 'SMS delivery failed.';
            return new Result([$errorMsg]);
        }

        return new Result($uuid);
    }

    /**
     * Validate the code the user submitted.
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
        $localPhone = $this->normalizePhone($phone_number);

        $url = $this->url
            . '?to='   . urlencode($localPhone)
            . '&msg='  . urlencode($messageBody)
            . '&user=' . urlencode($this->user)
            . '&key='  . urlencode($this->key);

        $body = $this->curlGet($url);

        if ($body === null || ($body['status'] ?? '') !== 'OK') {
            $errorMsg = $body['response'] ?? 'SMS delivery failed.';
            return new Result([$errorMsg]);
        }

        return new Result('sent');
    }

    /**
     * Make a GET request via cURL and return the decoded JSON body,
     * or null on failure.
     */
    private function curlGet(string $url): ?array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        curl_close($ch);

        if ($data === false || $data === '') {
            return null;
        }

        $decoded = json_decode($data, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Normalize any BD phone number format to 01xxxxxxxxx (11 digits).
     *
     * Accepts: +8801..., 8801..., 01..., 1...
     */
    private function normalizePhone(string $phone): string
    {
        // Strip whitespace and non-digit chars except leading +
        $phone = preg_replace('/[^\d+]/', '', $phone);

        if (str_starts_with($phone, '+880')) {
            $phone = '0' . substr($phone, 4);
        } elseif (str_starts_with($phone, '880')) {
            $phone = '0' . substr($phone, 3);
        }

        // If still missing leading 0 (bare 1XXXXXXXXX)
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
