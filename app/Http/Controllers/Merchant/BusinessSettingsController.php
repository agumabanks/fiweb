<?php

namespace App\Http\Controllers\Merchant;

use App\CentralLogics\helpers;
use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BusinessSettingsController extends Controller
{
    public function __construct(
        private Merchant $merchant,
    )
    {
    }

    /**
     * @return Application|Factory|View
     */
    public function shopIndex(): Factory|View|Application
    {
        $merchant = $this->merchant->where(['user_id' => auth()->user()->id])->first();
        return view('merchant-views.business-settings.shop-index', compact('merchant'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function shopUpdate(Request $request): RedirectResponse
    {
        $merchant = $this->merchant->where(['user_id' => auth()->user()->id])->first();

        if ($request->has('logo')) {
            $logo = Helpers::update('merchant/', $merchant->logo, 'png', $request->file('logo'));
        } else {
            $logo = $merchant['logo'];
        }

        $merchant->store_name = $request->store_name;
        $merchant->callback = $request->callback;
        $merchant->address = $request->address;
        $merchant->bin = $request->bin;
        $merchant->logo = $logo;
        $merchant->update();

        Toastr::success(translate('Successfully updated.'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function integrationIndex(): Factory|View|Application
    {
        $merchant = $this->merchant->where(['user_id' => auth()->user()->id])->first();
        return view('merchant-views.business-settings.integration-index', compact('merchant'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function integrationUpdate(Request $request): JsonResponse
    {
        $merchant = $this->merchant->where(['user_id' => auth()->user()->id])->first();
        $merchant->public_key = Str::random(50);
        $merchant->secret_key = Str::random(50);
        $merchant->update();

        return response()->json([
            'merchant' => $merchant,
            'success' => translate('Successfully updated.')
        ]);
    }


}
