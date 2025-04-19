<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Log;

class FirebaseController extends Controller
{
    /**
     * Show the main dashboard view with current Firebase status and control data.
     */
    public function dashboard(FirebaseService $firebase)
    {
        $status = $firebase->get('status') ?? [];
        $control = $firebase->get('control') ?? [];

        return view('dashboard', compact('status', 'control'));
    }

    /**
     * Handle toggle button changes (e.g., pump, buzzer, oled, etc.)
     */
    public function toggle(Request $request, FirebaseService $firebase)
    {
        Log::info('Toggle request received', $request->all());

        $request->validate([
            'key' => 'required|string',
            'value' => 'required'
        ]);

        try {
            $key = $request->input('key');
            $value = filter_var($request->input('value'), FILTER_VALIDATE_BOOLEAN);

            $firebase->set("control/{$key}", $value);

            Log::info('Toggle updated by user ID ' . auth()->id(), [
                'key' => $key,
                'value' => $value
            ]);

            return response()->json([
                'status' => 'updated',
                'key' => $key,
                'value' => $value,
                'control' => $firebase->get('control')
            ]);
        } catch (\Throwable $e) {
            Log::error('Firebase toggle error', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    /**
     * Switch between AUTO and MANUAL mode.
     */
    public function switchMode(Request $request, FirebaseService $firebase)
    {
        // 1) Validate as boolean. Accepts true, false, "1", "0", "true", "false"
        $request->validate([
            'mode' => 'required|boolean',
        ]);
    
        // 2) Cast to bool
        $mode = $request->boolean('mode');
    
        try {
            // 3) Write the boolean directly under "controls/mode"
            $firebase->set('control/mode', $mode);
    
            Log::info('Mode switched by user ID ' . auth()->id(), [
                'new_mode' => $mode,
            ]);
    
            // 4) Read back the entire "controls" node
            $controls = $firebase->get('controls');
    
            return response()->json([
                'status'  => 'mode switched',
                'mode'    => $mode,
                'controls'=> $controls,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to switch mode', [
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'error' => 'Could not switch mode',
            ], 500);
        }
    }
    

    /**
     * Save new Wi-Fi credentials to Firebase /control.
     */
    public function updateWifi(Request $request, FirebaseService $firebase)
    {
        $request->validate([
            'ssid' => 'required|string|max:32',
            'password' => 'required|string|max:64',
        ]);

        try {
            $ssid = $request->input('ssid');
            $password = $request->input('password');

            $firebase->set('control/ssid', $ssid);
            $firebase->set('control/password', $password);

            Log::info('Wi-Fi credentials updated by user ID ' . auth()->id(), [
                'ssid' => $ssid
            ]);

            return response()->json([
                'status' => 'Wi-Fi credentials updated',
                'control' => $firebase->get('control')
            ]);
        } catch (\Throwable $e) {
            Log::error('Wi-Fi update failed', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Could not update Wi-Fi'], 500);
        }
    }
}
