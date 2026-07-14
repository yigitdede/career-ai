<?php

namespace Tests\Unit;

use App\Services\SkillPassportBuilder;
use PHPUnit\Framework\TestCase;

class SkillPassportBuilderTest extends TestCase
{
    public function test_builds_radar_items_with_task_mapping_and_status(): void
    {
        $builder = new SkillPassportBuilder();

        $passport = $builder->build(
            [
                'radar' => [
                    ['label' => 'SQL', 'score' => 53, 'target' => 80],
                    ['label' => 'Python', 'score' => 70, 'target' => 75],
                ],
            ],
            [
                [
                    'id' => 'task-sql',
                    'title' => 'SQL portfolio',
                    'status' => 'pending',
                    'skill_impacts' => ['SQL'],
                    'has_evidence' => false,
                    'evidence_verified' => false,
                ],
                [
                    'id' => 'task-done',
                    'title' => 'Excel dashboard',
                    'status' => 'completed',
                    'skill_impacts' => ['Excel'],
                    'has_evidence' => false,
                    'evidence_verified' => false,
                ],
            ],
        );

        $this->assertSame(62, $passport['score']);
        $this->assertSame(['SQL', 'Python'], $passport['gaps']);
        $this->assertCount(2, $passport['items']);
        $this->assertSame(0, $passport['verified']);

        $sql = null;
        foreach ($passport['items'] as $item) {
            if ($item['skill'] === 'SQL') {
                $sql = $item;
            }
        }

        $this->assertNotNull($sql);
        $this->assertSame('task-sql', $sql['task_id']);
        $this->assertSame('waiting', $sql['status']);
    }

    public function test_marks_skill_verified_only_when_evidence_is_accepted(): void
    {
        $builder = new SkillPassportBuilder();

        $passport = $builder->build(
            [
                'radar' => [
                    ['label' => 'SAP ERP', 'score' => 50, 'target' => 75],
                ],
            ],
            [
                [
                    'id' => 'task-sap',
                    'title' => 'Complete SAP FI/CO advanced training',
                    'status' => 'completed',
                    'skill_impacts' => ['SAP ERP'],
                    'has_evidence' => false,
                    'evidence_verified' => false,
                ],
            ],
        );

        $this->assertSame('waiting', $passport['items'][0]['status']);
        $this->assertSame(0, $passport['verified']);
    }

    public function test_verified_skill_appears_when_evidence_was_accepted(): void
    {
        $builder = new SkillPassportBuilder();

        $passport = $builder->build(
            [
                'radar' => [
                    ['label' => 'Excel', 'score' => 60, 'target' => 80],
                ],
            ],
            [
                [
                    'id' => 'task-excel',
                    'title' => 'Excel dashboard',
                    'status' => 'completed',
                    'skill_impacts' => ['Excel'],
                    'has_evidence' => true,
                    'evidence_verified' => true,
                ],
            ],
        );

        $this->assertSame('verified', $passport['items'][0]['status']);
        $this->assertSame(1, $passport['verified']);
    }
}
