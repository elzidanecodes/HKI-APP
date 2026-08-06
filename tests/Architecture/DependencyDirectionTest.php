<?php

namespace Tests\Architecture;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Enforces ARCHITECTURE_BLUEPRINT.md §8.2's four dependency rules
 * (IMPLEMENTATION_PLAN.md Milestone M2.1) — the machine enforcement P8
 * requires: "aturan lapisan yang tidak ditegakkan mesin akan luntur dalam
 * hitungan bulan."
 *
 * app/Domain/ is empty as of this milestone. Rules 1-3 pass trivially
 * against the current (empty) tree and start catching real violations
 * the moment Milestone M2.2 onward adds Domain/Application code. Rule 4
 * has one tracked, pre-existing exception — see
 * test_models_have_no_untracked_conditional_logic_in_booted_hooks().
 *
 * This uses plain PHPUnit rather than Pest Architecture or Deptrac (both
 * named as acceptable options in ARCHITECTURE_BLUEPRINT.md §8.2): Deptrac
 * cannot express rules 2 and 4 (function-call and method-body content
 * checks, not namespace dependencies), and Pest would require installing
 * an entire second test runner for a single test file. A plain PHPUnit
 * test enforces all four rules with zero new dependencies.
 */
class DependencyDirectionTest extends TestCase
{
    private function projectPath(string $relative): string
    {
        return dirname(__DIR__, 2).'/'.$relative;
    }

    private function phpFilesUnder(string $relativeDir): array
    {
        $dir = $this->projectPath($relativeDir);

        if (! is_dir($dir)) {
            return [];
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );

        $files = [];

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    // --- Rule detectors -------------------------------------------------
    // Pure functions operating on file contents, unit-tested below against
    // inline fixtures before being applied to the real tree.

    private function domainForbiddenImports(string $contents): array
    {
        preg_match_all('/^use\s+([^\s;]+)\s*;/m', $contents, $matches);

        return array_values(array_filter($matches[1], function (string $import) {
            return str_starts_with($import, 'Illuminate\\')
                || str_starts_with($import, 'Filament\\')
                || str_starts_with($import, 'App\\Models\\');
        }));
    }

    private function domainForbiddenCalls(string $contents): array
    {
        $found = [];

        foreach (['now', 'auth', 'config', 'request'] as $function) {
            if (preg_match('/\b'.$function.'\s*\(/', $contents)) {
                $found[] = $function.'()';
            }
        }

        return $found;
    }

    private function filamentDisallowedDomainImports(string $contents): array
    {
        preg_match_all('/^use\s+(App\\\\Domain\\\\[^\s;]+)\s*;/m', $contents, $matches);

        return array_values(array_filter($matches[1], function (string $import) {
            return ! (str_contains($import, '\\Enums\\') || str_contains($import, '\\ValueObjects\\'));
        }));
    }

    private function bootedHookBody(string $contents): ?string
    {
        if (! preg_match('/function\s+booted\s*\([^)]*\)\s*(?::\s*\w+\s*)?\{/', $contents, $match, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $start = $match[0][1] + strlen($match[0][0]);
        $depth = 1;
        $length = strlen($contents);

        for ($i = $start; $i < $length; $i++) {
            if ($contents[$i] === '{') {
                $depth++;
            } elseif ($contents[$i] === '}') {
                $depth--;

                if ($depth === 0) {
                    return substr($contents, $start, $i - $start);
                }
            }
        }

        return null;
    }

    private function hasConditionalLogic(string $bootedBody): bool
    {
        return (bool) preg_match('/\b(if|switch|match)\s*\(/', $bootedBody);
    }

    // --- Detector unit tests (inline fixtures, not real files) ----------

    public function test_detects_forbidden_illuminate_import_in_domain_code(): void
    {
        $violating = "<?php\nnamespace App\\Domain\\Compliance;\nuse Illuminate\\Support\\Str;\nclass Example {}\n";

        $this->assertNotEmpty($this->domainForbiddenImports($violating));
    }

    public function test_detects_forbidden_filament_import_in_domain_code(): void
    {
        $violating = "<?php\nnamespace App\\Domain\\Compliance;\nuse Filament\\Forms\\Form;\nclass Example {}\n";

        $this->assertNotEmpty($this->domainForbiddenImports($violating));
    }

    public function test_detects_forbidden_model_import_in_domain_code(): void
    {
        $violating = "<?php\nnamespace App\\Domain\\Compliance;\nuse App\\Models\\Silos;\nclass Example {}\n";

        $this->assertNotEmpty($this->domainForbiddenImports($violating));
    }

    public function test_allows_shared_kernel_and_php_native_imports_in_domain_code(): void
    {
        $clean = "<?php\nnamespace App\\Domain\\Compliance;\nuse App\\Domain\\Shared\\ValueObjects\\DateRange;\nuse Carbon\\Carbon;\nclass Example {}\n";

        $this->assertEmpty($this->domainForbiddenImports($clean));
    }

    public function test_detects_forbidden_function_calls_in_domain_code(): void
    {
        $violating = "<?php\nnamespace App\\Domain\\Compliance;\nclass Example {\n    public function isValid() {\n        return now()->isPast();\n    }\n}\n";

        $this->assertNotEmpty($this->domainForbiddenCalls($violating));
    }

    public function test_allows_domain_code_with_time_injected_as_a_parameter(): void
    {
        $clean = "<?php\nnamespace App\\Domain\\Compliance;\nuse Carbon\\CarbonInterface;\nclass Example {\n    public function isValid(CarbonInterface \$today) {\n        return \$today->isPast();\n    }\n}\n";

        $this->assertEmpty($this->domainForbiddenCalls($clean));
    }

    public function test_detects_disallowed_domain_import_in_filament_code(): void
    {
        $violating = "<?php\nnamespace App\\Filament\\Resources;\nuse App\\Domain\\Operations\\Services\\AssignmentEligibility;\nclass Example {}\n";

        $this->assertNotEmpty($this->filamentDisallowedDomainImports($violating));
    }

    public function test_allows_filament_code_to_import_domain_enums_and_value_objects(): void
    {
        $clean = "<?php\nnamespace App\\Filament\\Resources;\nuse App\\Domain\\Compliance\\Enums\\DocumentStatus;\nuse App\\Domain\\Compliance\\ValueObjects\\DocumentValidity;\nclass Example {}\n";

        $this->assertEmpty($this->filamentDisallowedDomainImports($clean));
    }

    public function test_detects_conditional_logic_inside_a_booted_hook(): void
    {
        $violating = "<?php\nclass Example extends Model {\n    protected static function booted() {\n        static::saving(function (\$m) {\n            if (\$m->foo) {\n                \$m->bar = 1;\n            }\n        });\n    }\n}\n";

        $body = $this->bootedHookBody($violating);

        $this->assertNotNull($body);
        $this->assertTrue($this->hasConditionalLogic($body));
    }

    public function test_allows_a_booted_hook_with_no_conditional_logic(): void
    {
        $clean = "<?php\nclass Example extends Model {\n    protected static function booted() {\n        static::creating(fn (\$m) => \$m->uuid = (string) Str::uuid());\n    }\n}\n";

        $body = $this->bootedHookBody($clean);

        $this->assertNotNull($body);
        $this->assertFalse($this->hasConditionalLogic($body));
    }

    // --- Enforcement against the real tree -------------------------------

    public function test_domain_layer_has_no_forbidden_imports(): void
    {
        $violations = [];

        foreach ($this->phpFilesUnder('app/Domain') as $file) {
            foreach ($this->domainForbiddenImports(file_get_contents($file)) as $import) {
                $violations[] = "{$file} imports {$import}";
            }
        }

        $this->assertEmpty($violations, "Forbidden imports found in app/Domain:\n".implode("\n", $violations));
    }

    public function test_domain_layer_has_no_forbidden_function_calls(): void
    {
        $violations = [];

        foreach ($this->phpFilesUnder('app/Domain') as $file) {
            foreach ($this->domainForbiddenCalls(file_get_contents($file)) as $call) {
                $violations[] = "{$file} calls {$call}";
            }
        }

        $this->assertEmpty($violations, "Forbidden function calls found in app/Domain:\n".implode("\n", $violations));
    }

    public function test_filament_layer_only_imports_domain_enums_and_value_objects(): void
    {
        $violations = [];

        foreach ($this->phpFilesUnder('app/Filament') as $file) {
            foreach ($this->filamentDisallowedDomainImports(file_get_contents($file)) as $import) {
                $violations[] = "{$file} imports {$import}";
            }
        }

        $this->assertEmpty($violations, "Disallowed Domain imports found in app/Filament:\n".implode("\n", $violations));
    }

    public function test_models_have_no_untracked_conditional_logic_in_booted_hooks(): void
    {
        // TRACKED EXCEPTION — Silos::booted() currently mixes persistence
        // concerns with business logic (auto-computing tanggal_expired,
        // enforcing the one-active-SILO-per-equipment guard). This is
        // exactly what TECHNICAL_AUDIT.md flags (M6, M7, H2) and what
        // IMPLEMENTATION_PLAN.md Milestone M2.7 removes. Once M2.7 lands,
        // delete this exception — do not extend it to new files.
        $trackedExceptions = [
            'app/Models/Silos.php',
        ];

        $root = dirname(__DIR__, 2).'/';
        $violations = [];

        foreach ($this->phpFilesUnder('app/Models') as $file) {
            $body = $this->bootedHookBody(file_get_contents($file));

            if ($body === null || ! $this->hasConditionalLogic($body)) {
                continue;
            }

            $relative = str_replace($root, '', $file);

            if (in_array($relative, $trackedExceptions, true)) {
                continue;
            }

            $violations[] = $relative;
        }

        $this->assertEmpty($violations, "Untracked conditional logic in booted() hooks:\n".implode("\n", $violations));
    }
}
