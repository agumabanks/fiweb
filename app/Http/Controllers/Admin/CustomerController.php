<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\EMoney;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserLogHistory;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Stevebauman\Location\Facades\Location;

class CustomerController extends Controller
{
    public function __construct(
        private User           $user,
        private UserLogHistory $userLogHistory,
        private EMoney         $eMoney,
        private Transaction    $transaction
    )
    {
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request): Factory|View|Application
    {
        $ip = env('APP_MODE') == 'live' ? $request->ip() : '61.247.180.82';
        $currentUserInfo = Location::get($ip);
        return view('admin-views.customer.index', compact('currentUserInfo'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'f_name' => 'required',
            'l_name' => 'required',
            'image' => 'required',
            'country_code' => 'required',
            'phone' => [
                'required',
                Rule::unique('users')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
                'min:5',
                'max:20',
            ],
            'gender' => 'required',
            'occupation' => 'required',
            'password' => 'required|min:4|max:4',
        ], [
            'password.min' => 'Password must contain 4 characters',
            'password.max' => 'Password must contain 4 characters',
        ]);

        $phone = $request->country_code . $request->phone;
        $customer = $this->user->where(['phone' => $phone])->first();
        if (isset($customer)) {
            Toastr::warning(translate('This phone number is already taken'));
            return back();
        }

        DB::transaction(function () use ($request, $phone) {
            $user = $this->user;
            $user->f_name = $request->f_name;
            $user->l_name = $request->l_name;
            $user->image = Helpers::upload('customer/', 'png', $request->file('image'));
            $user->email = $request->email;
            $user->dial_country_code = $request->country_code;
            $user->phone = $phone;
            $user->gender = $request->gender;
            $user->occupation = $request->occupation;
            $user->password = bcrypt($request->password);
            $user->type = CUSTOMER_TYPE;
            $user->referral_id = $request->referral_id ?? null;
            $user->identification_image = json_encode([]);
            $user->save();

            $user->find($user->id);
            $user->unique_id = $user->id . mt_rand(1111, 99999);
            $user->save();

            $emoney = $this->eMoney;
            $emoney->user_id = $user->id;
            $emoney->save();
        });

        Toastr::success(translate('Customer Added Successfully!'));
        return redirect(route('admin.customer.list'));
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function customerList(Request $request): Factory|View|Application
    {
        $queryParams = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $customers = $this->user->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                }
            });
            $queryParams = ['search' => $request['search']];
        } else {
            $customers = $this->user;
        }

        $customers = $customers->latest()->customer()->paginate(Helpers::pagination_limit())->appends($queryParams);
        return view('admin-views.customer.list', compact('customers', 'search'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $key = explode(' ', $request['search']);
        $customers = $this->user->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('f_name', 'like', "%{$value}%")
                    ->orWhere('l_name', 'like', "%{$value}%")
                    ->orWhere('email', 'like', "%{$value}%")
                    ->orWhere('phone', 'like', "%{$value}%");
            }
        })->get();
        return response()->json([
            'view' => view('admin-views.customer.partials._table', compact('customers'))->render(),
        ]);
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function view($id): Factory|View|Application
    {
        $user = $this->user->with('emoney')->find($id);
        return view('admin-views.view.details', compact('user'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|Factory|View
     */
    public function transaction(Request $request, $id): Factory|View|Application
    {
        $queryParams = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);

            $users = $this->user->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%")
                        ->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                }
            })->get()->pluck('id')->toArray();

            $transactions = $this->transaction->where(function ($q) use ($key, $users) {
                foreach ($key as $value) {
                    $q->orWhereIn('from_user_id', $users)
                        ->orWhere('to_user_id', $users)
                        ->orWhere('transaction_type', 'like', "%{$value}%")
                        ->orWhere('transaction_id', 'like', "%{$value}%")
                        ->orWhere('balance', 'like', "%{$value}%");
                }
            });
            $queryParams = ['search' => $request['search']];
        } else {
            $transactions = $this->transaction;
        }

        $transactions = $transactions->where('user_id', $id)->latest()->paginate(Helpers::pagination_limit())->appends($queryParams);

        $user = $this->user->find($id);
        return view('admin-views.view.transaction', compact('user', 'transactions', 'search'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|Factory|View
     */
    public function log(Request $request, $id): View|Factory|Application
    {
        $queryParams = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $userLogs = $this->userLogHistory->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('ip_address', 'like', "%{$value}%")
                        ->orWhere('browser', 'like', "%{$value}%")
                        ->orWhere('os', 'like', "%{$value}%")
                        ->orWhere('device_model', 'like', "%{$value}%");
                }
            });
            $queryParams = ['search' => $request['search']];
        } else {
            $userLogs = $this->userLogHistory;
        }

        $userLogs = $userLogs->with(['user'])->where('user_id', $id)->latest()->paginate(Helpers::pagination_limit())->appends($queryParams);

        $user = $this->user->find($id);
        return view('admin-views.view.log', compact('user', 'userLogs', 'search'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $user = $this->user->find($request->id);
        $user->is_active = !$user->is_active;
        $user->save();
        Toastr::success(translate('Customer status updated!'));

        return back();
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id): Factory|View|Application
    {
        $customer = $this->user->find($id);
        return view('admin-views.customer.edit', compact('customer'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|RedirectResponse|Redirector
     */
    public function update(Request $request, $id): Redirector|Application|RedirectResponse
    {
        $request->validate([
            'f_name' => 'required',
            'l_name' => 'required',
            'occupation' => 'required',
        ]);

        $customer = $this->user->find($id);

        $customer->f_name = $request->f_name;
        $customer->l_name = $request->l_name;
        $customer->image = $request->has('image') ? Helpers::update('customer/', $customer->image, 'png', $request->file('image')) : $customer->image;
        $customer->email = $request->has('email') ? $request->email : $customer->email;
        $customer->gender = $request->has('gender') ? $request->gender : $customer->gender;
        $customer->occupation = $request->occupation;
        if ($request->has('password') && strlen($request->password) > 3) {
            $customer->password = bcrypt($request->password);
        }
        $customer->type = CUSTOMER_TYPE;
        $customer->referral_id = $request->referral_id ?? null;
        $customer->save();

        Toastr::success(translate('Customer updated successfully!'));
        return redirect(route('admin.customer.list'));
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function getKycRequest(Request $request): Factory|View|Application
    {
        $queryParams = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $customers = $this->user->where('is_kyc_verified', '!=', 1)->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                }
            });
            $queryParams = ['search' => $request['search']];
        } else {
            $customers = $this->user->where('is_kyc_verified', '!=', 1);
        }

        $customers = $customers->orderByDesc('id')->customer()->paginate(Helpers::pagination_limit())->appends($queryParams);
        return view('admin-views.customer.kyc_list', compact('customers', 'search'));
    }

    /**
     * @param $id
     * @param $status
     * @return RedirectResponse
     */
    public function updateKycStatus($id, $status): RedirectResponse
    {
        $user = $this->user->find($id);
        if (!isset($user)) {
            Toastr::error(translate('customer not found'));
            return back();
        }
        $user->is_kyc_verified = in_array($status, [1, 2]) ? $status : $user->is_kyc_verified;
        $user->save();

        $data = [
            'title' => $status == 1 ? translate('verification_request_is_accepted') : translate('verification_request_is_denied'),
            'description' => '',
            'image' => '',
            'order_id' => '',
        ];
        send_push_notification_to_device($user->fcm_token, $data);

        Toastr::success(translate('Successfully updated.'));
        return back();
    }

}
