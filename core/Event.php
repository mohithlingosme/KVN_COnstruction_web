<?php

declare(strict_types=1);

/**
 * KVN Construction - Simple Event System
 * 
 * Decouples business processes by allowing services to emit events
 * that are handled by listeners.
 * 
 * Usage:
 *   Event::dispatch('UserRegistered', ['user_id' => 123, 'email' => '...']);
 *   
 *   Event::listen('UserRegistered', function(array $payload) {
 *       // Send welcome email
 *   });
 */

class Event
{
    private static array $listeners = [];
    private static array $events = [];

    /**
     * Register a listener for an event
     */
    public static function listen(string $event, callable $listener, int $priority = 0): void
    {
        self::$listeners[$event][$priority][] = $listener;
        // Sort by priority (higher = executed first)
        krsort(self::$listeners[$event]);
    }

    /**
     * Dispatch an event with payload
     */
    public static function dispatch(string $event, array $payload = []): void
    {
        self::$events[] = [
            'event' => $event,
            'payload' => $payload,
            'time' => microtime(true),
        ];

        if (!isset(self::$listeners[$event])) {
            return;
        }

        foreach (self::$listeners[$event] as $priority => $listeners) {
            foreach ($listeners as $listener) {
                try {
                    call_user_func($listener, $payload);
                } catch (\Throwable $e) {
                    error_log("Event listener failed for {$event}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Remove all listeners for an event
     */
    public static function forget(string $event): void
    {
        unset(self::$listeners[$event]);
    }

    /**
     * Get all dispatched events
     */
    public static function getEvents(): array
    {
        return self::$events;
    }

    /**
     * Clear all events and listeners
     */
    public static function clear(): void
    {
        self::$listeners = [];
        self::$events = [];
    }
}

// ============================================
// REGISTER CORE EVENT LISTENERS
// ============================================

// Log all security-related events
Event::listen('UserRegistered', function (array $payload) {
    if (function_exists('logSecurityEvent')) {
        logSecurityEvent('USER_REGISTERED', 'User registered', ['email' => $payload['email'] ?? '']);
    }
});

Event::listen('UserLoggedIn', function (array $payload) {
    if (function_exists('logSecurityEvent')) {
        logSecurityEvent('USER_LOGIN', 'User logged in', ['user_id' => $payload['user_id'] ?? '']);
    }
});

Event::listen('OtpGenerated', function (array $payload) {
    if (function_exists('logSecurityEvent')) {
        logSecurityEvent('OTP_GENERATED', 'OTP generated', ['user_id' => $payload['user_id'] ?? '']);
    }
});

Event::listen('OtpVerified', function (array $payload) {
    if (function_exists('logSecurityEvent')) {
        logSecurityEvent('OTP_VERIFIED', 'OTP verified', ['user_id' => $payload['user_id'] ?? '']);
    }
});

Event::listen('PasswordChanged', function (array $payload) {
    if (function_exists('logSecurityEvent')) {
        logSecurityEvent('PASSWORD_CHANGED', 'Password changed', ['user_id' => $payload['user_id'] ?? '']);
    }
});

Event::listen('LeadCreated', function (array $payload) {
    if (function_exists('logSecurityEvent')) {
        logSecurityEvent('LEAD_CREATED', 'Lead created', ['lead_id' => $payload['lead_id'] ?? '']);
    }
});

Event::listen('ProjectCreated', function (array $payload) {
    if (function_exists('logSecurityEvent')) {
        logSecurityEvent('PROJECT_CREATED', 'Project created', ['project_id' => $payload['project_id'] ?? '']);
    }
});

Event::listen('PaymentReceived', function (array $payload) {
    if (function_exists('logSecurityEvent')) {
        logSecurityEvent('PAYMENT_RECEIVED', 'Payment received', ['amount' => $payload['amount'] ?? '']);
    }
});

Event::listen('MediaUploaded', function (array $payload) {
    if (function_exists('logSecurityEvent')) {
        logSecurityEvent('MEDIA_UPLOADED', 'Media uploaded', ['filename' => $payload['filename'] ?? '']);
    }
});