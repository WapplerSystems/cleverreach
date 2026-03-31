<?php
declare(strict_types=1);

namespace WapplerSystems\Cleverreach\Service;

/**
 * Request-scoped singleton that holds the CleverReach settings read from a
 * form's renderingOptions.cleverreach.* during the EXT:form submission pipeline.
 *
 * Populated by AfterSubmitHook before validators run.
 * Read by OptinValidator and OptoutValidator via GeneralUtility::makeInstance().
 */
final class CleverreachFormContext
{
    private ?array $settings = null;

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    public function isInitialized(): bool
    {
        return $this->settings !== null;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }
}