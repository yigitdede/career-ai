<?php

namespace Tests\Unit;

use App\Services\BuilderCvTextExporter;
use PHPUnit\Framework\TestCase;

class BuilderCvTextExporterTest extends TestCase
{
    public function test_exports_builder_locales_to_text(): void
    {
        $text = BuilderCvTextExporter::toText($this->draft(), 'tr');

        $this->assertGreaterThan(40, strlen($text));
        $this->assertStringContainsString('Test Candidate', $text);
        $this->assertStringContainsString('SQL', $text);
    }

    public function test_builds_builder_filename(): void
    {
        $name = BuilderCvTextExporter::fileName($this->draft(), 'tr');

        $this->assertSame('test-candidate-builder.json', $name);
    }

    /** @return array<string, array<string, mixed>> */
    private function draft(): array
    {
        return [
            'tr' => [
                'personal' => [
                    'full_name' => 'Test Candidate',
                    'email' => 'candidate@example.test',
                    'summary' => 'SQL ve veri modelleme deneyimine sahip test adayı.',
                ],
                'experience' => [],
                'education' => [],
                'skills' => [['category' => 'Teknik', 'items' => 'SQL, PHP']],
                'projects' => [],
                'certificates' => [],
            ],
        ];
    }
}
