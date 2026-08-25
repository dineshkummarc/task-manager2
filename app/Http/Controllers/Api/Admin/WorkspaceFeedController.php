<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\UserActivity;
use Illuminate\Http\Request;

class WorkspaceFeedController extends Controller
{
    public function index(Request $request)
    {
        $v = $request->validate(['per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);

        return ActivityResource::collection(UserActivity::with('user')->latest()->paginate($v['per_page'] ?? 30));
    }
}
