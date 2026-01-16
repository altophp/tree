<?php

declare(strict_types=1);

/*
 * This file is part of the ALTO library.
 *
 * © 2026–present Simon André
 *
 * For full copyright and license information, please see
 * the LICENSE file distributed with this source code.
 */

namespace Alto\Tree\Printer;

/**
 * Configuration options for tree printing.
 *
 * This class centralizes all printer configuration options and provides
 * validation and defaults.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
readonly class PrinterOptions
{
    public function __construct(
        public string $style = 'default',           // default, compact, detailed, icons
        public bool $showHidden = true,             // Show hidden files
        public bool $filesOnly = false,             // Show only files
        public bool $dirsOnly = false,              // Show only directories
        public ?string $pattern = null,             // Filter by pattern (e.g., '*.php')
        public ?int $maxDepth = null,               // Limit depth
        public bool $showSize = false,              // Show file sizes
        public bool $showDate = false,              // Show modification dates
        public bool $showPermissions = false,       // Show permissions
        public ?string $sortBy = null,              // name, size, date, type, null = no sorting
        public string $sortOrder = 'asc',           // asc, desc
        public bool $colors = false,                // Use ANSI colors
    ) {
    }

    /**
     * Create from array of options.
     *
     * @param array<string, mixed> $options
     */
    public static function fromArray(array $options): self
    {
        /** @var string $style */
        $style = $options['style'] ?? 'default';
        /** @var bool $showHidden */
        $showHidden = $options['show_hidden'] ?? true;
        /** @var bool $filesOnly */
        $filesOnly = $options['files_only'] ?? false;
        /** @var bool $dirsOnly */
        $dirsOnly = $options['dirs_only'] ?? false;
        /** @var string|null $pattern */
        $pattern = $options['pattern'] ?? null;
        /** @var int|null $maxDepth */
        $maxDepth = $options['max_depth'] ?? null;
        /** @var bool $showSize */
        $showSize = $options['show_size'] ?? false;
        /** @var bool $showDate */
        $showDate = $options['show_date'] ?? false;
        /** @var bool $showPermissions */
        $showPermissions = $options['show_permissions'] ?? false;
        /** @var string|null $sortBy */
        $sortBy = $options['sort_by'] ?? null;
        /** @var string $sortOrder */
        $sortOrder = $options['sort_order'] ?? 'asc';
        /** @var bool $colors */
        $colors = $options['colors'] ?? false;

        return new self(
            style: $style,
            showHidden: $showHidden,
            filesOnly: $filesOnly,
            dirsOnly: $dirsOnly,
            pattern: $pattern,
            maxDepth: $maxDepth,
            showSize: $showSize,
            showDate: $showDate,
            showPermissions: $showPermissions,
            sortBy: $sortBy,
            sortOrder: $sortOrder,
            colors: $colors,
        );
    }

    /**
     * Check if this node should be displayed based on filters.
     */
    public function shouldDisplay(string $name, bool $isDir): bool
    {
        if (!$this->showHidden && str_starts_with($name, '.')) {
            return false;
        }

        if ($this->filesOnly && $isDir) {
            return false;
        }
        if ($this->dirsOnly && !$isDir) {
            return false;
        }

        if (null !== $this->pattern && !fnmatch($this->pattern, $name)) {
            return false;
        }

        return true;
    }
}
