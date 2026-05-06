<?php

namespace Tests\Unit;

use App\Helpers\RecurringOrderHelper;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecurringOrderHelperTest extends TestCase
{
    private const DAILY = 1;
    private const TWO_WEEKS = 14;
    private const FOUR_WEEKS = 28;
    #[Test]
    #[DataProvider('futureProvider')]
    public function it_calculates_next_charge_date_when_candidate_is_in_future(
        Carbon $previous,
        int $frequency,
        string $today,
        string $expected
    ) {
        Carbon::setTestNow($today);

        $result = RecurringOrderHelper::calculateNextChargeDateByFrequency(
            $previous,
            $frequency
        );

        $this->assertEquals($expected, $result->toDateString());
    }

    public static function futureProvider(): array
    {
        return [
            'daily_future' => [
                Carbon::parse('2025-01-01'),
                self::DAILY,
                '2025-01-01',
                '2025-01-02',
            ],

            'two_weeks_future' => [
                Carbon::parse('2025-01-01'),
                self::TWO_WEEKS,
                '2025-01-01',
                '2025-01-15',
            ],

            'four_weeks_future' => [
                Carbon::parse('2025-01-01'),
                self::FOUR_WEEKS,
                '2025-01-01',
                '2025-01-29',
            ],
        ];
    }
    #[Test]
    #[DataProvider('todayProvider')]
    public function it_shifts_next_charge_date_forward_if_candidate_is_today(
        Carbon $previous,
        int $frequency,
        string $today,
        string $expected
    ) {
        Carbon::setTestNow($today);

        $result = RecurringOrderHelper::calculateNextChargeDateByFrequency(
            $previous,
            $frequency
        );

        $this->assertEquals($expected, $result->toDateString());
    }

    public static function todayProvider(): array
    {
        return [
            'daily_today' => [
                Carbon::parse('2025-01-01'),
                self::DAILY,
                '2025-01-02',
                '2025-01-03',
            ],

            'two_weeks_today' => [
                Carbon::parse('2025-01-01'),
                self::TWO_WEEKS,
                '2025-01-15',
                '2025-01-29',
            ],

            'four_weeks_today' => [
                Carbon::parse('2025-01-01'),
                self::FOUR_WEEKS,
                '2025-01-29',
                '2025-02-26',
            ],
        ];
    }
    #[Test]
    #[DataProvider('pastProvider')]
    public function it_shifts_next_charge_date_forward_if_candidate_is_in_past(
        Carbon $previous,
        int $frequency,
        string $today,
        string $expected
    ) {
        Carbon::setTestNow($today);

        $result = RecurringOrderHelper::calculateNextChargeDateByFrequency(
            $previous,
            $frequency
        );

        $this->assertEquals($expected, $result->toDateString());
    }

    public static function pastProvider(): array
    {
        return [
            'daily_past' => [
                Carbon::parse('2025-01-01'),
                self::DAILY,
                '2025-01-05',
                '2025-01-06',
            ],

            'two_weeks_past' => [
                Carbon::parse('2025-01-01'),
                self::TWO_WEEKS,
                '2025-02-10',
                '2025-02-24',
            ],

            'four_weeks_past' => [
                Carbon::parse('2025-01-01'),
                self::FOUR_WEEKS,
                '2025-02-20',
                '2025-03-20',
            ],
        ];
    }
}
