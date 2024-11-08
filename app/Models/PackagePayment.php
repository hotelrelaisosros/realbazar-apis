<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackagePayment extends Model
{
    protected $appends = ["duration", 'counter'];
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    protected function getDurationAttribute()
    {
        $counter = 0;
        $counter = now()->diffInDays(Carbon::parse($this->end_date));
        $duration = 0;
        $start_date = Carbon::parse($this->start_date);
        $end_date = Carbon::parse($this->end_date);
        $years = $end_date->diffInYears($start_date);
        if ($years > 0) {
            $duration = $duration . $years . ' years ';
            $start_date = $start_date->addYears($years);
        }
        $months = $end_date->diffInMonths($start_date);
        if ($months > 0) {
            $duration = $duration . $months . ' months ';
            $start_date = $start_date->addMonths($months);
        }
        $days = $end_date->diffInDays($start_date);
        if ($days > 0) {
            $duration = $duration . $days . ' days ';
            $start_date = $start_date->addDays($days);
        }
        $hours = $end_date->diffInHours($start_date);
        if ($hours > 0) {
            $duration = $duration . $hours . ' hours ';
            $start_date = $start_date->addHours($hours);
        }
        $minutes = $end_date->diffInMinutes($start_date);
        if ($minutes > 0) {
            $duration = $duration . $minutes . ' minutes ';
            $start_date = $start_date->addMinutes($minutes);
        }
        $seconds = $end_date->diffInSeconds($start_date);
        if ($seconds > 0) {
            $duration = $duration . $seconds . ' seconds ';
            $start_date = $start_date->addSeconds($seconds);
        }
        return $duration;
    }

    protected function getCounterAttribute()
    {
        $counter = 0;
        $days = now()->diffInDays(Carbon::parse($this->end_date), false);
        if ($days > 0) {
            $counter = $counter . $days . ' days ';
        }

        return (int)$counter;
    }
}
