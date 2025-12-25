<?php
return [
    'time' => (function () {
        $times = [];
        $startHour = 5; // Giờ bắt đầu
        $endHour = 23; // Giờ kết thúc
        $interval = 30; // Khoảng cách mỗi lần lặp (phút)

        $index = 1;
        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            $times[$index++] = sprintf('%02d:00', $hour); // Thời điểm 00 phút
            if ($hour < $endHour) {
                $times[$index++] = sprintf('%02d:30', $hour); // Thời điểm 30 phút
            }
        }
        return $times;
    })(),
];
