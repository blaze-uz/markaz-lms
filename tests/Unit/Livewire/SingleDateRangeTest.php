<?php

namespace Tests\Unit\Livewire;

use App\Livewire\Payment\PaymentList;
use Carbon\Carbon;
use ReflectionMethod;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * The date-range pickers put "d.m.Y - d.m.Y" into the component, but a
 * user who clicks one day and stops leaves just "d.m.Y" there. Sixteen places
 * read the second half without looking, so the payments page answered that
 * click with "Undefined array key 1" (PaymentList.php).
 * A single day now means that whole day, as it already did in HasDateRange,
 * ExpensesReport and LeadController.
 */
class SingleDateRangeTest extends TestCase
{
    public function test_a_single_picked_day_is_that_whole_day(): void
    {
        [$from, $to] = $this->rangeOf('19.09.2026');

        $this->assertSame('2026-09-19 00:00:00', $from->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-19 23:59:59', $to->format('Y-m-d H:i:s'));
    }

    public function test_a_full_range_is_read_as_before(): void
    {
        [$from, $to] = $this->rangeOf('01.09.2026 - 19.09.2026');

        $this->assertSame('2026-09-01 00:00:00', $from->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-19 23:59:59', $to->format('Y-m-d H:i:s'));
    }

    /**
     * The same one-token bug was copied into sixteen components. Keep a new copy
     * from coming back: every read of the second half of an exploded range is
     * either defaulted (`?? $dates[0]`) or follows a count()/isset() guard a few
     * lines above it (an `if` around it, or an early return).
     */
    public function test_no_date_range_reads_its_second_half_unguarded(): void
    {
        $unguarded = [];

        foreach ((new Finder)->files()->in(app_path())->name('*.php') as $file) {
            $lines = file($file->getRealPath());

            foreach ($lines as $i => $line) {
                if (! preg_match('/\$(dates|dateRange)\[1\]/', $line) || str_contains($line, '??')) {
                    continue;
                }

                $before = implode('', array_slice($lines, max(0, $i - 8), min(8, $i)));
                if (preg_match('/count\(\$(dates|dateRange)\)|isset\(\$(dates|dateRange)\[1\]\)/', $before)) {
                    continue;
                }

                $unguarded[] = $file->getRelativePathname().':'.($i + 1);
            }
        }

        $this->assertSame([], $unguarded);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function rangeOf(string $value): array
    {
        $component = new PaymentList;
        $component->date = $value;

        $method = new ReflectionMethod(PaymentList::class, 'getDateRange');
        $method->setAccessible(true);

        return $method->invoke($component);
    }
}
