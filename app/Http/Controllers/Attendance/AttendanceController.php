<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(): View
    {
        return view('attendance.index');
    }
}