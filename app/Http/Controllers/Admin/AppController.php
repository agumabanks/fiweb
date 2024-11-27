<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AppController extends Controller
{
    /**
     * Display a listing of the apps.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Retrieve all apps with pagination
        $apps = App::with('addedBy')->paginate(10);

        // Pass the data to the view
        return view('admin-views.apps.index', compact('apps'))->with('pageTitle', 'Sanaa Apps');
    }

    /**
     * Show the form for creating a new app.
     *
     * @return \Illuminate\View\View
     */
    public function addApp()
    {
        return view('admin-views.apps.add')->with('pageTitle', 'Add New App');
    }

    /**
     * Store a newly created app in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
     
     public function store(Request $request)
{
    // $request->validate([
    //     'name' => 'required|string|max:255',
    //     'description' => 'nullable|string',
    //     'version' => 'nullable|string|max:50',
    //     'file' => 'required|file|mimes:apk,ipa,zip,', // File validation: max size 10MB
    // ]);

    // File handling logic
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $filePath = $file->store('sanaaApps', 'public');

        // Save app data to the database
        App::create([
            'name' => $request->name,
            'description' => $request->description,
            'version' => $request->version,
            'file_path' => $filePath,
            'added_by' => auth()->user()->id,
        ]);

        return redirect()->route('admin.apps.index')->with('success', 'App uploaded successfully!');
    }

    return redirect()->back()->with('error', 'App upload failed.');
}

/**
 * Download the specified app file.
 *
 * @param  int  $id
 * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
 */
    public function download($id)
    {
        // Find the app by its ID
        $app = App::findOrFail($id);
    
        // Get the file path from the database
        $filePath = $app->file_path;
    
        // Check if the file exists in the storage
        if (Storage::disk('public')->exists($filePath)) {
            // Return the file as a download response
            return Storage::disk('public')->download($filePath, basename($filePath));
        }
    
        // Redirect back with an error if the file does not exist
        return redirect()->back()->with('error', 'File not found.');
    }


    public function store10(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'version' => 'nullable|string|max:50',
            'file' => 'required|file|mimes:zip,apk,ipa,exe|max:10240', // Adjust MIME types and file size as necessary
        ]);

        // Handle the file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Define the storage path
            $path = 'apps'; // This stores files inside 'storage/app/public/apps' folder

            // Store the file with a unique name and in the 'public' disk
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs($path, $fileName, 'public');

            // Store app details in the database
            App::create([
                'name' => $request->name,
                'description' => $request->description,
                'version' => $request->version,
                'file_path' => $filePath, // Path to the uploaded file
                'added_by' => Auth::id(), // Store the user ID who added the app
            ]);

            // Redirect back with success message
            return redirect()->route('admin.apps.index')->with('success', 'App uploaded successfully!');
        }

        // If no file was uploaded, return an error
        return redirect()->back()->with('error', 'Failed to upload app. Please ensure the file is valid.');
    }
}
