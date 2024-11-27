<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\helpers;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class BannerController extends Controller
{
    public function __construct(
        private Banner $banner
    )
    {
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    function index(Request $request): View|Factory|Application
    {
        $queryParams = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $banner = $this->banner->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('title', 'like', "%{$value}%");
                }
            });
            $queryParams = ['search' => $request['search']];
        } else {
            $banner = $this->banner;
        }

        $banners = $banner->latest()->paginate(Helpers::pagination_limit())->appends($queryParams);
        return view('admin-views.banner.index', compact('banners', 'search'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required',
            'url' => 'required',
            'image' => 'mimes:jpeg,jpg,png,gif|required|max:10000'
        ]);

        try {
            $banner = $this->banner;
            $banner->title = $request->title;
            $banner->url = $request->url;
            $banner->image = $request->has('image') ? Helpers::upload('banner/', 'png', $request->file('image')) : null;
            $banner->status = 1;
            $banner->receiver = $request->has('receiver') ? $request->receiver : null;
            $banner->save();

        } catch (\Exception $e) {
            Toastr::warning(translate('Banner added failed'));
            return back();
        }

        Toastr::success(translate('Banner added successfully'));
        return back();
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id): Factory|View|Application
    {
        $banner = $this->banner->find($id);
        return view('admin-views.banner.edit', compact('banner'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|RedirectResponse|Redirector
     */
    public function update(Request $request, $id): Redirector|Application|RedirectResponse
    {
        $request->validate([
            'title' => 'required',
            'url' => 'required',
            'image' => 'mimes:jpeg,jpg,png,gif|max:10000'
        ]);

        $banner = $this->banner->find($id);
        $banner->title = $request->title;
        $banner->url = $request->url;
        $banner->image = $request->has('image') ? Helpers::update('banner/', $banner->image, 'png', $request->file('image')) : $banner->image;
        $banner->receiver = $request->has('receiver') ? $request->receiver : $banner->receiver;
        $banner->save();
        Toastr::success(translate('Banner updated successfully'));
        return redirect(route('admin.banner.index'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $banner = $this->banner->find($request->id);
        $banner->status = !$banner->status;
        $banner->save();
        Toastr::success(translate('Banner status updated'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $banner = $this->banner->find($request->id);
        Helpers::delete('banner/' . $banner['image']);
        $banner->delete();
        Toastr::success(translate('Banner removed'));
        return back();
    }
}
