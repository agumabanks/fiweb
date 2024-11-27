<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\helpers;
use App\Http\Controllers\Controller;
use App\Models\UserLogHistory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private UserLogHistory $userLogHistory,
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function log(Request $request): Factory|View|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $userLogs = $this->userLogHistory->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('ip_address', 'like', "%{$value}%")
                        ->orWhere('device_id', 'like', "%{$value}%")
                        ->orWhere('browser', 'like', "%{$value}%")
                        ->orWhere('os', 'like', "%{$value}%")
                        ->orWhere('device_model', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        } else {
            $userLogs = $this->userLogHistory;
        }

        $userLogs = $userLogs->with(['user'])->latest()->paginate(Helpers::pagination_limit())->appends($queryParam);
        return view('admin-views.user.log-list', compact('userLogs', 'search'));
    }
}
