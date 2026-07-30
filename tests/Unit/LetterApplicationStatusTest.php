<?php

namespace Tests\Unit;

use App\Enums\LetterApplicationStatus;
use PHPUnit\Framework\TestCase;

class LetterApplicationStatusTest extends TestCase
{
    public function test_only_legal_status_transitions_are_allowed(): void
    {
        $this->assertTrue(LetterApplicationStatus::Submitted->canTransitionTo(LetterApplicationStatus::UnderReview));
        $this->assertFalse(LetterApplicationStatus::Submitted->canTransitionTo(LetterApplicationStatus::Completed));
        $this->assertTrue(LetterApplicationStatus::Completed->isTerminal());
    }
}
