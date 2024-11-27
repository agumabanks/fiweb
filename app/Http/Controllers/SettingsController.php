<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SettingController;

class SettingsController extends Controller
{
    /**
     * Show settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $settings = SettingController::all();
        return view('admin-views.settings.index', compact('settings'));
    }

    /**
     * Update settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'default_period' => 'required|in:daily,weekly,monthly,custom',
        ]);

        // Update or create the setting
        Setting::updateOrCreate(
            ['key' => 'default_period'],
            ['value' => $request->input('default_period')]
        );

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Get a specific setting by key.
     *
     * @param string $key
     * @return string|null
     */
    public function getSetting($key)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : null;
    }
}
