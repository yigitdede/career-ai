<?php

namespace Tests\Unit;

use App\Services\BuilderCvTextExporter;
use App\Data\PanelDemoData;
use PHPUnit\Framework\TestCase;

class BuilderCvTextExporterTest extends TestCase
{
    public function test_exports_builder_locales_to_text(): void
    {
        $text = BuilderCvTextExporter::toText(PanelDemoData::cvDraft(), 'tr');

        $this->assertGreaterThan(40, strlen($text));
        $this->assertStringContainsString('Ayşe Yılmaz', $text);
        $this->assertStringContainsString('SQL', $text);
    }

    public function test_builds_builder_filename(): void
    {
        $name = BuilderCvTextExporter::fileName(PanelDemoData::cvDraft(), 'tr');

        $this->assertSame('ayse-yilmaz-builder.json', $name);
    }
}
