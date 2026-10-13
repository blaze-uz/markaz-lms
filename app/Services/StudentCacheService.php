<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class StudentCacheService
{
    public static function getStatsCacheKey(?int $branchId): string
    {
        return $branchId ? "student_card_stats_{$branchId}" : 'student_card_stats';
    }

    public static function getIndexCardsCacheKey(?int $branchId): string
    {
        return $branchId ? "student_index_cards_stats_{$branchId}" : 'student_index_cards_stats';
    }

    /**
     * Clear all student-related cache
     */
    public static function clearCache(?int $branchId = null): void
    {
        if ($branchId) {
            // Clear dashboard cache
            Cache::forget("students_count_{$branchId}");
            Cache::forget("groups_count_{$branchId}");
            Cache::forget("courses_count_{$branchId}");

            // Clear student table cache
            Cache::forget(self::getStatsCacheKey($branchId));
            Cache::forget(self::getIndexCardsCacheKey($branchId));

            // Log cache clearing for debugging
            \Illuminate\Support\Facades\Log::info("Cleared student cache for branch: {$branchId}");
        } else {
            // Clear cache for all branches if branch not specified
            $cacheKeys = Cache::get('all_student_cache_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            \Illuminate\Support\Facades\Log::info('Cleared all student cache keys');
        }

        self::forgetLegacyStudentStatsCache();
    }

    /**
     * Track cache keys for later clearing
     */
    public static function trackCacheKey(string $key): void
    {
        $cacheKeys = Cache::get('all_student_cache_keys', []);
        if (! in_array($key, $cacheKeys)) {
            $cacheKeys[] = $key;
            Cache::put('all_student_cache_keys', $cacheKeys, now()->addDays(30));
        }
    }

    /**
     * Clear student index card stats and related caches after group_student pivot changes
     * (attach/detach do not fire Student model updated events).
     *
     * @param  iterable<int, Student>|Student  $students
     * @param  Group|iterable<int, Group>|null  $groups  Branches tied to the group(s) must be cleared too
     */
    public static function invalidateAfterStudentGroupMembershipChange(iterable|Student $students, Group|iterable|null $groups = null): void
    {
        self::invalidateStudentIndexStatsForBranches(self::collectBranchIds($students, $groups));
    }

    /**
     * Clear student-related stats cache using the student's current branch context.
     */
    public static function invalidateForStudent(Student $student): void
    {
        self::invalidateStudentIndexStatsForBranches(self::collectBranchIds($student));
    }

    /**
     * @param  array<int|string>  $branchIds
     */
    public static function invalidateStudentIndexStatsForBranches(array $branchIds): void
    {
        if ($branchIds === []) {
            self::clearCache(null);
        } else {
            foreach ($branchIds as $branchId) {
                self::clearCache((int) $branchId);
            }
        }

        self::forgetLegacyStudentStatsCache();
    }

    private static function forgetLegacyStudentStatsCache(): void
    {
        Cache::forget('student_card_stats');
        Cache::forget('student_index_cards_stats');
        Cache::forget('students_count');
        if (Auth::check()) {
            Cache::forget('student_card_stats'.Auth::id());
        }
    }

    /**
     * @param  iterable<int, Student>|Student  $students
     * @param  Group|iterable<int, Group>|null  $groups
     * @return array<int>
     */
    private static function collectBranchIds(iterable|Student $students, Group|iterable|null $groups = null): array
    {
        $studentCollection = $students instanceof Student ? [$students] : $students;

        $branchIds = [];
        foreach ($studentCollection as $student) {
            if (! $student instanceof Student) {
                continue;
            }

            $branchIds = array_merge($branchIds, $student->branches()->pluck('branches.id')->all());
        }

        if ($groups !== null) {
            $groupCollection = $groups instanceof Group ? [$groups] : $groups;
            foreach ($groupCollection as $group) {
                if (! $group instanceof Group) {
                    continue;
                }

                $branchIds = array_merge($branchIds, $group->branches()->pluck('branches.id')->all());
            }
        }

        if (Auth::check() && Auth::user()->current_branch_id) {
            $branchIds[] = Auth::user()->current_branch_id;
        }

        return array_values(array_unique(array_filter(
            array_map(static fn ($id): int => (int) $id, $branchIds),
            static fn (int $id): bool => $id > 0,
        )));
    }
}
