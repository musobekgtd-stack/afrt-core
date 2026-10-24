<?php

declare(strict_types=1);

namespace Afrt\Core\DTO;

/**
 * Data transfer object encapsulating the validation verdict of an allocation request.
 */
final class ValidationResult
{
    private bool $isValid;
    private float $maxPermittedHours;
    private float $currentHours;
    private float $requestedHours;
    private float $resultingHours;
    private float $remainingHours;
    private float $fteUtilizationPercent;
    /** @var string[] */
    private array $violations;
    /** @var string[] */
    private array $warnings;

    /**
     * @param string[] $violations
     * @param string[] $warnings
     */
    public function __construct(
        bool $isValid,
        float $maxPermittedHours,
        float $currentHours,
        float $requestedHours,
        float $resultingHours,
        float $remainingHours,
        float $fteUtilizationPercent,
        array $violations = [],
        array $warnings = []
    ) {
        $this->isValid = $isValid;
        $this->maxPermittedHours = round($maxPermittedHours, 2);
        $this->currentHours = round($currentHours, 2);
        $this->requestedHours = round($requestedHours, 2);
        $this->resultingHours = round($resultingHours, 2);
        $this->remainingHours = round($remainingHours, 2);
        $this->fteUtilizationPercent = round($fteUtilizationPercent, 2);
        $this->violations = $violations;
        $this->warnings = $warnings;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function getMaxPermittedHours(): float
    {
        return $this->maxPermittedHours;
    }

    public function getCurrentHours(): float
    {
        return $this->currentHours;
    }

    public function getRequestedHours(): float
    {
        return $this->requestedHours;
    }

    public function getResultingHours(): float
    {
        return $this->resultingHours;
    }

    public function getRemainingHours(): float
    {
        return $this->remainingHours;
    }

    public function getFteUtilizationPercent(): float
    {
        return $this->fteUtilizationPercent;
    }

    /**
     * @return string[]
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    /**
     * @return string[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'isValid' => $this->isValid,
            'maxPermittedHours' => $this->maxPermittedHours,
            'currentHours' => $this->currentHours,
            'requestedHours' => $this->requestedHours,
            'resultingHours' => $this->resultingHours,
            'remainingHours' => $this->remainingHours,
            'fteUtilizationPercent' => $this->fteUtilizationPercent,
            'violations' => $this->violations,
            'warnings' => $this->warnings,
        ];
    }
}
