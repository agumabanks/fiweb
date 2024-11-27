<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private Notification $notification
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    function index(Request $request): Factory|View|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $notifications = $this->notification->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('title', 'like', "%{$value}%")
                        ->orWhere('description', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        } else {
            $notifications = $this->notification;
        }


        $notifications = $notifications->latest()->paginate(Helpers::pagination_limit())->appends($queryParam);
        return view('admin-views.notification.index', compact('notifications', 'search'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required',
            'receiver' => 'required',
            'description' => 'required'
        ], [
            'title.required' => 'title is required!',
        ]);

        $notification = $this->notification;
        $notification->title = $request->title;
        $notification->receiver = $request->receiver;
        $notification->description = $request->description;
        $notification->image = $request->has('image') ? Helpers::upload('notification/', 'png', $request->file('image')) : null;
        $notification->status = 1;
        $notification->save();

        $data = [];
        $data['title'] = $request->title;
        $data['description'] = $request->description;
        $data['image'] = '';
        $data['receiver'] = strtolower($request->receiver);
        try {
            if ($request->receiver == 'all' || $request->receiver == 'customers' || $request->receiver == 'agents') {
                Helpers::send_push_notif_to_topic($data);

            } else {
                throw new \Exception();
            }

        } catch (\Exception $e) {
            Toastr::warning('Push notification failed!');
        }

        Toastr::success('Notification sent successfully!');
        return back();
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id): View|Factory|Application
    {
        $notification = $this->notification->find($id);
        return view('admin-views.notification.edit', compact('notification'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ], [
            'title.required' => 'title is required!',
        ]);

        $oldNotification = $this->notification->find($id);
        $notification = $this->notification;
        $notification->title = $request->title;
        $notification->description = $request->description;
        $notification->receiver = $request->has('receiver') ? $request->receiver : $oldNotification->receiver;
        $notification->image = $request->has('image') ? Helpers::update('notification/', $oldNotification->image, 'png', $request->file('image')) : $oldNotification->image;
        $notification->save();

        $data = [];
        $data['title'] = $request->has('title') ? $request->title : $oldNotification->title;
        $data['description'] = $request->has('description') ? $request->description : $oldNotification->description;
        $data['image'] = '';
        $data['receiver'] = strtolower($request->has('receiver') ? $request->receiver : $oldNotification->receiver);
        try {
            if ($request->receiver == 'all' || $request->receiver == 'customers' || $request->receiver == 'agents') {
                Helpers::send_push_notif_to_topic($data);
                Toastr::success('Notification resend successfully!');

            } else {
                throw new \Exception();
            }

        } catch (\Exception $e) {
            Toastr::warning('Push notification failed!');
        }

        return redirect()->route('admin.notification.add-new');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $notification = $this->notification->find($request->id);
        $notification->status = $request->status;
        $notification->save();
        Toastr::success('Notification status updated!');
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $notification = $this->notification->find($request->id);
        Helpers::delete('notification/' . $notification['image']);
        $notification->delete();
        Toastr::success('Notification removed!');
        return back();
    }
}
