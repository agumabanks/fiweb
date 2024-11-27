<?php

namespace App\Http\Controllers;

use App\CentralLogics\Helpers;
use App\Models\Purpose;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class PurposeController extends Controller
{
    public function __construct(
        private Purpose $purpose
    )
    {
    }

    /**
     * @return Application|Factory|View
     */
    public function index(): Factory|View|Application
    {
        $purposes = $this->purpose->paginate(Helpers::pagination_limit());
        return view('admin-views.purpose.index', compact('purposes'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse|void
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required',
                'logo' => 'required',
                'color' => 'required',
            ]);
            $purpose = $this->purpose;
            $purpose->title = $request->title;
            $purpose->logo = Helpers::upload('purpose/', 'png', $request->file('logo'));
            $purpose->color = $request->color;
            $purpose->save();

            Toastr::success(translate('Successfully Added!'));
            return back();
        } catch (Exception $e) {
            Toastr::error(translate('failed!'));
        }

    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function edit(Request $request): Factory|View|Application
    {
        $purpose = $this->purpose->find($request->id);
        return view('admin-views.purpose.edit', compact('purpose'));
    }

    /**
     * @param Request $request
     * @return Application|RedirectResponse|Redirector
     */
    public function update(Request $request): Redirector|Application|RedirectResponse
    {
        try {
            $request->validate([
                'title' => 'required',
                'color' => 'required',
            ]);
            $purpose = $this->purpose->find($request->id);
            $purpose->title = $request->title;
            $purpose->logo = $request->has('logo') ? Helpers::update('purpose/', $purpose->logo, 'png', $request->file('logo')) : $purpose->logo;
            $purpose->color = $request->color;
            $purpose->save();

            Toastr::success(translate('Successfully Updated!'));
        } catch (Exception $e) {
            Toastr::error(translate('failed!'));
        }
        return redirect(route('admin.purpose.index'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $purpose = $this->purpose->find($request->id);
        unlink('storage/app/public/purpose/' . $purpose->logo);
        $purpose->delete();

        Toastr::success(translate('Successfully Deleted!'));
        return back();
    }
}
