<?php
class DateHelper {
    public static function formatSmartDate($dateString) {
        if (empty($dateString)) return '-';
        
        $dates = is_array($dateString) ? $dateString : json_decode($dateString, true);
        
        if (!is_array($dates)) {
            // Try to handle single date or plain string
            if (is_string($dateString) && strtotime($dateString)) {
                return date('d M Y', strtotime($dateString));
            }
            return is_string($dateString) ? $dateString : '-';
        }
        if (empty($dates)) return '-';

        sort($dates);
        
        $groups = [];
        if (count($dates) > 0) {
            $currentGroup = [$dates[0]];
            
            for ($i = 1; $i < count($dates); $i++) {
                $prev = new DateTime($dates[$i-1]);
                $curr = new DateTime($dates[$i]);
                
                $interval = $prev->diff($curr);
                
                if ($interval->days == 1) {
                    $currentGroup[] = $dates[$i];
                } else {
                    $groups[] = $currentGroup;
                    $currentGroup = [$dates[$i]];
                }
            }
            $groups[] = $currentGroup;
        }
        
        $outputParts = [];
        
        // Check if all dates are in the same year
        $firstYear = (new DateTime($dates[0]))->format('Y');
        $sameYear = true;
        foreach($dates as $d) {
            if ((new DateTime($d))->format('Y') !== $firstYear) {
                $sameYear = false;
                break;
            }
        }

        foreach ($groups as $group) {
            $start = new DateTime($group[0]);
            $end = new DateTime($group[count($group) - 1]);
            
            if (count($group) == 1) {
                if ($sameYear) {
                    $outputParts[] = $start->format('j M');
                } else {
                    $outputParts[] = $start->format('j M Y');
                }
            } else {
                if ($sameYear) {
                     $outputParts[] = $start->format('j M') . ' - ' . $end->format('j M');
                } else {
                     // If range spans years (e.g. 31 Dec 2025 - 2 Jan 2026)
                     if ($start->format('Y') == $end->format('Y')) {
                         $outputParts[] = $start->format('j M') . ' - ' . $end->format('j M Y');
                     } else {
                         $outputParts[] = $start->format('j M Y') . ' - ' . $end->format('j M Y');
                     }
                }
            }
        }
        
        $finalString = implode(', ', $outputParts);
        if ($sameYear) {
            $finalString .= ' ' . $firstYear;
        }
        
        return $finalString;
    }
    public static function formatSmartDateIndonesian($dateString) {
        if (empty($dateString)) return '-';
        
        $dates = is_array($dateString) ? $dateString : json_decode($dateString, true);
        
        if (!is_array($dates)) {
            if (is_string($dateString) && strtotime($dateString)) {
                return self::formatIndonesianDate($dateString);
            }
            return is_string($dateString) ? $dateString : '-';
        }
        if (empty($dates)) return '-';

        sort($dates);
        
        $groups = [];
        if (count($dates) > 0) {
            $currentGroup = [$dates[0]];
            
            for ($i = 1; $i < count($dates); $i++) {
                $prev = new DateTime($dates[$i-1]);
                $curr = new DateTime($dates[$i]);
                
                $interval = $prev->diff($curr);
                
                if ($interval->days == 1) {
                    $currentGroup[] = $dates[$i];
                } else {
                    $groups[] = $currentGroup;
                    $currentGroup = [$dates[$i]];
                }
            }
            $groups[] = $currentGroup;
        }
        
        $outputParts = [];
        $months = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun',
            '07' => 'Jul', '08' => 'Agu', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
        ];

        // Check if all dates are in the same year
        $firstYear = (new DateTime($dates[0]))->format('Y');
        $sameYear = true;
        foreach($dates as $d) {
            if ((new DateTime($d))->format('Y') !== $firstYear) {
                $sameYear = false;
                break;
            }
        }

        foreach ($groups as $group) {
            $start = new DateTime($group[0]);
            $end = new DateTime($group[count($group) - 1]);
            
            $startD = $start->format('d');
            $startM = $months[$start->format('m')];
            $startY = $start->format('Y');
            
            $endD = $end->format('d');
            $endM = $months[$end->format('m')];
            $endY = $end->format('Y');
            
            if (count($group) == 1) {
                if ($sameYear) {
                    $outputParts[] = "$startD $startM";
                } else {
                    $outputParts[] = "$startD $startM $startY";
                }
            } else {
                if ($sameYear) {
                     if ($startM == $endM) {
                         $outputParts[] = "$startD - $endD $startM";
                     } else {
                         $outputParts[] = "$startD $startM - $endD $endM";
                     }
                } else {
                     if ($startY == $endY) {
                         $outputParts[] = "$startD $startM - $endD $endM $endY";
                     } else {
                         $outputParts[] = "$startD $startM $startY - $endD $endM $endY";
                     }
                }
            }
        }
        
        $finalString = implode(', ', $outputParts);
        if ($sameYear) {
            $finalString .= ' ' . $firstYear;
        }
        
        return $finalString;
    }

    public static function formatIndonesianDate($dateString) {
        if (empty($dateString)) return '-';
        
        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];
        
        $date = date('d', strtotime($dateString));
        $month = date('m', strtotime($dateString));
        $year = date('Y', strtotime($dateString));
        
        return $date . ' ' . $months[$month] . ' ' . $year;
    }

    public static function getShortMonthIndonesian($dateString) {
        $months = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun',
            '07' => 'Jul', '08' => 'Agu', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
        ];
        $m = date('m', strtotime($dateString));
        return $months[$m] ?? date('M', strtotime($dateString));
    }

    public static function formatDateTimeShort($timestamp) {
        if (!$timestamp) return '-';
        return date('d M Y, H:i', $timestamp);
    }

    public static function addWorkingDays($startDate, $daysToAdd, $holidays = []) {
        $date = new DateTime($startDate);
        $daysAdded = 0;

        while ($daysAdded < $daysToAdd) {
            $date->modify('+1 day');
            $dayOfWeek = $date->format('N'); // 1 (Mon) to 7 (Sun)
            $dateString = $date->format('Y-m-d');

            // Check if weekend (6=Sat, 7=Sun) or holiday
            if ($dayOfWeek >= 6 || in_array($dateString, $holidays)) {
                continue;
            }

            $daysAdded++;
        }

        return $date->format('Y-m-d');
    }

    public static function countWorkingDays($startDate, $endDate, $holidays = []) {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        
        if ($start > $end) return 0;

        $workingDays = 0;
        // Iterate from start+1 to end (H-x logic typically counts days *notice*)
        // If today is 23rd, and event is 24th (H-1). 
        // If we want difference in working days.
        // Let's iterate until we reach end.
        
        // Clone start to avoid modifying it if passed by ref (objects are ref in PHP 5+, but safe here)
        $curr = clone $start;
        
        while ($curr < $end) {
            $curr->modify('+1 day');
            $dayOfWeek = $curr->format('N');
            $dateString = $curr->format('Y-m-d');
            
            if ($dayOfWeek >= 6 || in_array($dateString, $holidays)) {
                continue;
            }
            $workingDays++;
        }
        
        return $workingDays;
    }
}
?>