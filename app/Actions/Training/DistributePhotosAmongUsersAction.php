<?php

namespace App\Actions\Training;

class DistributePhotosAmongUsersAction
{
    /**
     * @param  array<int, int>  $userCounts  userId => photoCount
     * @return array<int, int> userId => takeCount
     */
    public function run(array $userCounts, int $limit): array
    {
        $totalAvailable = array_sum($userCounts);
        if ($totalAvailable <= $limit) {
            return $userCounts;
        }

        if ($totalAvailable === 0) {
            return [];
        }

        $ratio = $limit / $totalAvailable;
        $takeCounts = [];
        $remainders = [];

        foreach ($userCounts as $userId => $count) {
            $exact = $count * $ratio;
            $takeCounts[$userId] = (int) floor($exact);
            $remainders[$userId] = $exact - $takeCounts[$userId];
        }

        $currentTotal = array_sum($takeCounts);
        $diff = $limit - $currentTotal;

        if ($diff > 0) {
            arsort($remainders);
            foreach (array_keys($remainders) as $userId) {
                if ($diff <= 0) {
                    break;
                }

                if ($takeCounts[$userId] < $userCounts[$userId]) {
                    $takeCounts[$userId]++;
                    $diff--;
                }
            }
        }

        return $takeCounts;
    }
}
