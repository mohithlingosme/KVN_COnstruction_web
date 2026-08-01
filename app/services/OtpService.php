<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OtpRepository;

/**
 * OTP Service - Business logic for OTP generation and verification.
 * No SQL in this layer - all data access delegated to OtpRepository.
 */
class OTPService
{
    private OtpRepository $otpRepo;

    public function __construct(?OtpRepository $repo = null)
    {
        $this->otpRepo = $repo ?? new OtpRepository();
    }

    /**
     * Generate a secure 6-digit OTP, persist it, and trigger send.
     */
    public function generateAndSendOTP(string $phone, ?int $userId = null, ?string $ipAddress = null): bool
    {
        // 1. Invalidate any existing unverified OTPs for this phone
        $this->otpRepo->invalidateByPhone($phone);

        // 2. Generate a secure 6-digit OTP
        $otp = (string) random_int(100000, 999999);
        $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);

        // 3. Set expiration (10 minutes from now)
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // 4. Save to database via repository
        $saved = $this->otpRepo->create($phone, $userId, $hashedOtp, $expiresAt, $ipAddress);

        if (!$saved) {
            return false;
        }

        /*
         * TODO: Integrate Actual SMS/WhatsApp Gateway Here (e.g., Twilio, Msg91)
         * For local testing, we will just return the raw OTP or log it.
         */
        error_log("TESTING ONLY - OTP for $phone is: $otp");

        return true; // Successfully generated and "sent"
    }

    /**
     * Verify an OTP submitted by the user.
     */
    public function verifyOTP(string $phone, string $inputOtp): array
    {
        // Find the latest active OTP for this phone via repository
        $record = $this->otpRepo->findActiveByPhone($phone);

        if (!$record) {
            return ['success' => false, 'message' => 'No active OTP found.'];
        }

        // Check expiration
        if (strtotime($record['expires_at']) < time()) {
            return ['success' => false, 'message' => 'OTP has expired. Please request a new one.'];
        }

        // Verify the hash
        if (password_verify($inputOtp, $record['otp_hash'])) {
            // Mark as used via repository
            $this->otpRepo->markUsed((int) $record['id']);

            return ['success' => true, 'message' => 'OTP Verified successfully.'];
        }

        return ['success' => false, 'message' => 'Invalid OTP.'];
    }
}