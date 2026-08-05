<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SettingsRepository;
use App\Repositories\CmsRepository;

/**
 * Admin Settings Service - Business logic for admin settings pages.
 *
 * Handles validation, insert-or-update semantics, and password hashing.
 * All SQL delegation goes to SettingsRepository (and CmsRepository for SEO).
 */
class AdminSettingsService
{
    private SettingsRepository $settingsRepo;
    private CmsRepository $cmsRepo;

    public function __construct(?SettingsRepository $settingsRepo = null, ?CmsRepository $cmsRepo = null)
    {
        $this->settingsRepo = $settingsRepo ?? new SettingsRepository();
        $this->cmsRepo = $cmsRepo ?? new CmsRepository();
    }

    /**
     * Standard result array.
     */
    private function result(bool $ok, string $message): array
    {
        return [
            'success' => $ok,
            'message' => $message,
        ];
    }

    // ========================================================================
    // GENERAL SETTINGS
    // ========================================================================

    public function getGeneralSettings(): array
    {
        $settings = $this->settingsRepo->getGeneralSettings();
        return $settings ?: [];
    }

    public function saveGeneralSettings(array $data): array
    {
        // Validation
        $required = ['site_name', 'site_tagline', 'admin_email', 'support_email', 'phone', 'whatsapp', 'address'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                return $this->result(false, 'Please fill all fields.');
            }
        }

        $settings = [
            'site_name'         => trim((string) ($data['site_name'] ?? '')),
            'site_tagline'      => trim((string) ($data['site_tagline'] ?? '')),
            'admin_email'       => trim((string) ($data['admin_email'] ?? '')),
            'support_email'     => trim((string) ($data['support_email'] ?? '')),
            'phone'             => trim((string) ($data['phone'] ?? '')),
            'whatsapp'          => trim((string) ($data['whatsapp'] ?? '')),
            'address'           => trim((string) ($data['address'] ?? '')),
            'facebook_link'     => trim((string) ($data['facebook_link'] ?? '')),
            'instagram_link'    => trim((string) ($data['instagram_link'] ?? '')),
            'youtube_link'      => trim((string) ($data['youtube_link'] ?? '')),
            'linkedin_link'     => trim((string) ($data['linkedin_link'] ?? '')),
            'logo'              => trim((string) ($data['logo'] ?? '')),
            'favicon'           => trim((string) ($data['favicon'] ?? '')),
            'footer_text'       => trim((string) ($data['footer_text'] ?? '')),
            'maintenance_mode'  => in_array(($data['maintenance_mode'] ?? 'off'), ['on', 'off'], true) ? $data['maintenance_mode'] : 'off',
        ];

        if ($this->settingsRepo->generalSettingsExist()) {
            $ok = $this->settingsRepo->updateGeneralSettings($settings);
        } else {
            $ok = $this->settingsRepo->insertGeneralSettings($settings);
        }

        return $this->result($ok, $ok ? 'General settings updated successfully.' : 'Failed to update general settings.');
    }

    // ========================================================================
    // SEO SETTINGS (delegated to CmsRepository / AdminCmsService pattern)
    // ========================================================================

    public function getSeoSettings(): array
    {
        $settings = $this->cmsRepo->getSeoSettings();
        return $settings ?: [];
    }

    public function saveSeoSettings(array $data): array
    {
        $required = ['meta_title', 'meta_description', 'meta_keywords', 'canonical_url'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                return $this->result(false, 'Please fill all required fields.');
            }
        }

        $settings = [
            'meta_title'                => trim((string) ($data['meta_title'] ?? '')),
            'meta_description'          => trim((string) ($data['meta_description'] ?? '')),
            'meta_keywords'             => trim((string) ($data['meta_keywords'] ?? '')),
            'canonical_url'             => trim((string) ($data['canonical_url'] ?? '')),
            'robots_meta'               => trim((string) ($data['robots_meta'] ?? '')),
            'google_analytics'          => trim((string) ($data['google_analytics'] ?? '')),
            'google_search_console'     => trim((string) ($data['google_search_console'] ?? '')),
            'facebook_meta_title'       => trim((string) ($data['facebook_meta_title'] ?? '')),
            'facebook_meta_description' => trim((string) ($data['facebook_meta_description'] ?? '')),
            'twitter_meta_title'        => trim((string) ($data['twitter_meta_title'] ?? '')),
            'twitter_meta_description'  => trim((string) ($data['twitter_meta_description'] ?? '')),
            'sitemap_status'            => in_array(($data['sitemap_status'] ?? 'enabled'), ['enabled', 'disabled'], true) ? $data['sitemap_status'] : 'enabled',
            'seo_status'                => in_array(($data['seo_status'] ?? 'enabled'), ['enabled', 'disabled'], true) ? $data['seo_status'] : 'enabled',
        ];

        if ($this->cmsRepo->seoSettingsExists()) {
            $ok = $this->cmsRepo->updateSeoSettings($settings);
        } else {
            $ok = $this->cmsRepo->insertSeoSettings($settings);
        }

        return $this->result($ok, $ok ? 'SEO settings updated successfully.' : 'Failed to update SEO settings.');
    }

    // ========================================================================
    // SMTP SETTINGS (key-value `settings` table, group=smtp)
    // ========================================================================

    public function getSmtpSettings(): array
    {
        return $this->settingsRepo->getAllAsMap('smtp');
    }

    public function saveSmtpSettings(array $data): array
    {
        $smtpHost = trim((string) ($data['smtp_host'] ?? ''));
        $smtpPort = (int) ($data['smtp_port'] ?? 587);
        $smtpEncryption = trim((string) ($data['smtp_encryption'] ?? 'tls'));
        $smtpUsername = trim((string) ($data['smtp_username'] ?? ''));
        $smtpPassword = (string) ($data['smtp_password'] ?? '');
        $smtpFromEmail = trim((string) ($data['smtp_from_email'] ?? ''));
        $smtpFromName = trim((string) ($data['smtp_from_name'] ?? ''));

        if ($smtpHost === '' || $smtpPort <= 0 || $smtpUsername === '' || $smtpFromEmail === '' || $smtpFromName === '') {
            return $this->result(false, 'Please fill all required SMTP fields.');
        }

        $settings = [
            'smtp_host'        => $smtpHost,
            'smtp_port'        => (string) $smtpPort,
            'smtp_encryption'  => $smtpEncryption,
            'smtp_username'    => $smtpUsername,
            'smtp_from_email'  => $smtpFromEmail,
            'smtp_from_name'   => $smtpFromName,
        ];

        // Only update password if provided
        if ($smtpPassword !== '') {
            $settings['smtp_password'] = $smtpPassword;
        }

        $ok = $this->settingsRepo->setMultiple($settings, 'smtp');
        return $this->result($ok, $ok ? 'SMTP settings saved successfully.' : 'Failed to save SMTP settings.');
    }

    // ========================================================================
    // SMS SETTINGS
    // ========================================================================

    public function getSmsSettings(): array
    {
        $settings = $this->settingsRepo->getSmsSettings();
        return $settings ?: [];
    }

    public function saveSmsSettings(array $data): array
    {
        $required = ['sms_provider', 'api_key', 'sender_id', 'auth_token', 'api_url', 'admin_mobile'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                return $this->result(false, 'Please fill all required fields.');
            }
        }

        $settings = [
            'sms_provider'        => trim((string) ($data['sms_provider'] ?? '')),
            'api_key'             => trim((string) ($data['api_key'] ?? '')),
            'sender_id'           => trim((string) ($data['sender_id'] ?? '')),
            'auth_token'          => trim((string) ($data['auth_token'] ?? '')),
            'api_url'             => trim((string) ($data['api_url'] ?? '')),
            'admin_mobile'        => trim((string) ($data['admin_mobile'] ?? '')),
            'sms_status'          => in_array(($data['sms_status'] ?? 'enabled'), ['enabled', 'disabled'], true) ? $data['sms_status'] : 'enabled',
            'notify_contact_form' => in_array(($data['notify_contact_form'] ?? 'yes'), ['yes', 'no'], true) ? $data['notify_contact_form'] : 'yes',
            'notify_new_lead'     => in_array(($data['notify_new_lead'] ?? 'yes'), ['yes', 'no'], true) ? $data['notify_new_lead'] : 'yes',
        ];

        if ($this->settingsRepo->smsSettingsExist()) {
            $ok = $this->settingsRepo->updateSmsSettings($settings);
        } else {
            $ok = $this->settingsRepo->insertSmsSettings($settings);
        }

        return $this->result($ok, $ok ? 'SMS settings updated successfully.' : 'Failed to update SMS settings.');
    }

    // ========================================================================
    // INTEGRATION SETTINGS
    // ========================================================================

    public function getIntegrationSettings(): array
    {
        $settings = $this->settingsRepo->getIntegrationSettings();
        return $settings ?: [];
    }

    public function saveIntegrationSettings(array $data): array
    {
        $required = ['whatsapp_number', 'youtube_channel', 'instagram_url', 'linkedin_url'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                return $this->result(false, 'Please fill all required fields.');
            }
        }

        $settings = [
            'google_maps_api'              => trim((string) ($data['google_maps_api'] ?? '')),
            'google_recaptcha_site_key'    => trim((string) ($data['google_recaptcha_site_key'] ?? '')),
            'google_recaptcha_secret_key'  => trim((string) ($data['google_recaptcha_secret_key'] ?? '')),
            'facebook_pixel_id'            => trim((string) ($data['facebook_pixel_id'] ?? '')),
            'whatsapp_number'              => trim((string) ($data['whatsapp_number'] ?? '')),
            'youtube_channel'              => trim((string) ($data['youtube_channel'] ?? '')),
            'instagram_url'                => trim((string) ($data['instagram_url'] ?? '')),
            'linkedin_url'                 => trim((string) ($data['linkedin_url'] ?? '')),
            'telegram_url'                 => trim((string) ($data['telegram_url'] ?? '')),
            'chatbot_status'               => in_array(($data['chatbot_status'] ?? 'disabled'), ['enabled', 'disabled'], true) ? $data['chatbot_status'] : 'disabled',
            'recaptcha_status'             => in_array(($data['recaptcha_status'] ?? 'enabled'), ['enabled', 'disabled'], true) ? $data['recaptcha_status'] : 'enabled',
            'whatsapp_chat_status'         => in_array(($data['whatsapp_chat_status'] ?? 'enabled'), ['enabled', 'disabled'], true) ? $data['whatsapp_chat_status'] : 'enabled',
        ];

        if ($this->settingsRepo->integrationSettingsExist()) {
            $ok = $this->settingsRepo->updateIntegrationSettings($settings);
        } else {
            $ok = $this->settingsRepo->insertIntegrationSettings($settings);
        }

        return $this->result($ok, $ok ? 'Integration settings updated successfully.' : 'Failed to update integration settings.');
    }

    // ========================================================================
    // SECURITY SETTINGS
    // ========================================================================

    public function getSecuritySettings(): array
    {
        $settings = $this->settingsRepo->getSecuritySettings();
        return $settings ?: [];
    }

    public function saveSecuritySettings(array $data): array
    {
        $adminUsername = trim((string) ($data['admin_username'] ?? ''));
        $adminEmail = trim((string) ($data['admin_email'] ?? ''));
        $newPassword = (string) ($data['new_password'] ?? '');
        $confirmPassword = (string) ($data['confirm_password'] ?? '');

        if ($adminUsername === '' || $adminEmail === '') {
            return $this->result(false, 'Please fill all required fields.');
        }

        if ($newPassword !== '' && $newPassword !== $confirmPassword) {
            return $this->result(false, 'Passwords do not match.');
        }

        // Load current settings to preserve the existing password hash
        $current = $this->settingsRepo->getSecuritySettings();
        $currentPassword = (string) ($current['admin_password'] ?? '');

        // Hash new password if provided
        if ($newPassword !== '') {
            $currentPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $settings = [
            'admin_username'       => $adminUsername,
            'admin_email'          => $adminEmail,
            'admin_password'       => $currentPassword,
            'session_timeout'      => (int) ($data['session_timeout'] ?? 30),
            'login_attempt_limit'  => (int) ($data['login_attempt_limit'] ?? 5),
            'two_factor_auth'      => in_array(($data['two_factor_auth'] ?? 'disabled'), ['enabled', 'disabled'], true) ? $data['two_factor_auth'] : 'disabled',
            'maintenance_lock'     => in_array(($data['maintenance_lock'] ?? 'disabled'), ['enabled', 'disabled'], true) ? $data['maintenance_lock'] : 'disabled',
        ];

        if ($this->settingsRepo->securitySettingsExist()) {
            $ok = $this->settingsRepo->updateSecuritySettings($settings);
        } else {
            $ok = $this->settingsRepo->insertSecuritySettings($settings);
        }

        return $this->result($ok, $ok ? 'Security settings updated successfully.' : 'Failed to update security settings.');
    }
}

