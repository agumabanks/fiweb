<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;

class BranchesController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'branch_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        // Create a new branch
        $branch = Branch::create($validatedData);

        // Flash success message
        return redirect()->route('admin.allbranches')->with('success', 'Branch created successfully.');
    }

    // Optional: Add a form to create a new branch
    public function create()
    {
        return view('admin-views.branches.create'); // Create a form view for adding new branches
    }

    // Optional: Display all branches
    public function index()
    {
        $branches = Branch::all();
        return view('admin-views.branches.index', compact('branches'));
    }
}
