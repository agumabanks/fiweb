<?php

namespace App\Http\Controllers\Admin;

use App\Models\SocialMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;

class SocialMediaController extends Controller
{

    /**
     * @return Application|Factory|View
     */
    public function index(): View|Factory|Application
    {
        return view('admin-views.social-media.social-media');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            SocialMedia::updateOrInsert([
                'name' => $request->get('name'),
            ], [
                'name' => $request->get('name'),
                'link' => $request->get('link'),
            ]);

            return response()->json([
                'success' => 1,
            ]);

        } catch (\Exception $exception) {
            return response()->json([
                'error' => 1,
            ]);
        }
    }

    /**
     * @param $socialMedia
     * @return JsonResponse
     */
    public function show($socialMedia): JsonResponse
    {
        $data = SocialMedia::where('id', $socialMedia)->first();
        return response()->json($data);
    }

    /**
     * @param SocialMedia $socialMedia
     * @return JsonResponse
     */
    public function edit(SocialMedia $socialMedia): JsonResponse
    {
        return response()->json($socialMedia);
    }


    /**
     * @param Request $request
     * @param $socialMedia
     * @return JsonResponse
     */
    public function update(Request $request, $socialMedia): JsonResponse
    {
        $socialMedia = SocialMedia::find($socialMedia);
        $socialMedia->name = $request->name;
        $socialMedia->link = $request->link;
        $socialMedia->save();
        return response()->json();
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function fetch(Request $request)
    {
        if ($request->ajax()) {
            $data = SocialMedia::orderBy('id', 'desc')->get()
            ->map(function ($data) {
                return [
                    'id' => $data->id,
                    'name' => translate($data->name),
                    'link' => $data->link,
                    'status' => $data->status,
                ];
            });

            return response()->json($data);
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function socialMediaStatusUpdate(Request $request): RedirectResponse
    {
        $data=SocialMedia::find($request?->id);
        $data->status =  $data->status ? 0 :1;
        $data?->save();
        if($data->status == 1){
            Toastr::success(Str::title($data->name).' '.translate('is_Enabled!'));
        } else{
            Toastr::warning(Str::title($data->name).' '.translate('is_Disabled!'));
        }
        return back();
    }
}
