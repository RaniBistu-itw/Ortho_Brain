<?php

use App\Models\Practice;
use App\Support\ActivePractice;

if (! function_exists('currentPractice')) {
    /**
     * The doctor's currently active practice for this session.
     * Returns null when no doctor is logged in or the doctor has no approved practices.
     */
    function currentPractice(): ?Practice
    {
        return ActivePractice::get();
    }
}
