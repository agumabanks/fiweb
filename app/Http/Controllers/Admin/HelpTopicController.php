<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\helpers;
use App\Http\Controllers\Controller;
use App\Models\HelpTopic;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HelpTopicController extends Controller
{
    public function __construct(
        private HelpTopic $helpTopic
    ){}

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'question' => 'required|unique:help_topics',
            'answer'   => 'required',
            'ranking'   => 'required',
        ], [
            'question.required' => 'Question name is required!',
            'answer.required'   => 'Question answer is required!',
            'ranking.required'   => 'Question ranking is required!',

        ]);
        $helps = $this->helpTopic;
        $helps->question = $request->question;
        $helps->answer = $request->answer;
        $helps->status = $request->status??0;
        $helps->ranking = $request->ranking;
        $helps->save();

        Toastr::success('FAQ added successfully!');
        return back();
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function status($id): JsonResponse
    {
        $helps = $this->helpTopic->findOrFail($id);
        $helps->update(["status" => !$helps->status]);
        return response()->json(['success' => 'Status Change']);

    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function edit($id): JsonResponse
    {
        $helps = $this->helpTopic->findOrFail($id);
        return response()->json($helps);
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'question' => 'required',
            'answer'   => 'required',
            'ranking' => 'required',
        ]);
        $helps = $this->helpTopic->find($id);
        $helps->question = $request->question;
        $helps->answer = $request->answer;
        $helps->ranking = $request->ranking;
        $helps->update();
        Toastr::success('FAQ Update successfully!');
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    function list(): Factory|View|Application
    {
        $helps = $this->helpTopic->orderBy('ranking', 'ASC')->paginate(Helpers::pagination_limit());
        return view('admin-views.help-topics.list', compact('helps'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $helps = $this->helpTopic->find($request->id);
        $helps->delete();
        return response()->json();
    }
}
