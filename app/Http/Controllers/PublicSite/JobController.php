<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Job;

class JobController extends Controller
{
    public function index()
    {
        return response()->json(
            Job::latest()->get()
        );
    }

    public function show(Job $job)
    {
        return response()->json($job);
    }
}
