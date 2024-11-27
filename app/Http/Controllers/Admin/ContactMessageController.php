<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\CentralLogics\helpers;
use App\Models\ContactMessage;
use App\Http\Controllers\Controller;
use App\Mail\CustomerMessageMail;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;

class ContactMessageController extends Controller
{
    public function list(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $queryParams = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $contacts = ContactMessage::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%")
                        ->orWhere('mobile_number', 'like', "%{$value}%");
                }
            });
            $queryParams = ['search' => $request['search']];
        } else {
            $contacts = new ContactMessage();
        }
        $contacts = $contacts->latest()->paginate(Helpers::pagination_limit())->appends($queryParams);
        return view('admin-views.contact-message.index', compact('contacts', 'search'));
    }

    public function view($id): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $contact = ContactMessage::findOrFail($id);
        return view('admin-views.contact-message.view', compact('contact'));
    }


    public function destroy(Request $request): \Illuminate\Http\RedirectResponse
    {
        $contact = ContactMessage::find($request->id);
        $contact->delete();
        Toastr::success(translate('Message Delete successfully'));
        return redirect()->route('admin.contact.list');
    }

    public function sendMail(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $contact = ContactMessage::findOrFail($id);
        $data = array('body' => $request['mail_body'],
            'name' => $contact->name
        );

        try {
            Mail::to($contact['email'])->send(new CustomerMessageMail($request, $data));

            $contact->update([
                'reply' => json_encode([
                    'subject' => $request['subject'],
                    'body' => $request['mail_body']
                ]),
                'seen' => 1,
            ]);
            Toastr::success(translate('Mail_sent_successfully'));
            return back();
        } catch (\Throwable $th) {
            Toastr::error(translate('Something_went_wrong_please_check_your_mail_config'));
            return back();
        }

    }
}
