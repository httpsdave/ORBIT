<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Inertia\Inertia;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Enable 2FA for the user
     */
    public function enable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided password is incorrect.',
                'errors' => [
                    'password' => ['The provided password is incorrect.']
                ]
            ], 422);
        }

        // Generate a new secret
        $secret = $this->google2fa->generateSecretKey();

        // Generate QR code URL (otpauth://)
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        // Generate QR code as SVG image using BaconQrCode v3
        $renderer = new ImageRenderer(
            new RendererStyle(200, 0),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);
        
        // Convert SVG to base64 data URL for easy embedding
        $qrCodeImage = 'data:image/svg+xml;base64,' . base64_encode($qrCodeSvg);

        // Generate recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();

        // Store encrypted secret and recovery codes (not enabled yet)
        $user->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => $recoveryCodes,
        ]);

        // Return as JSON response for the frontend
        return response()->json([
            'success' => true,
            'secret' => $secret,
            'qr_code_url' => $qrCodeImage,
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Confirm and activate 2FA
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();

        if (!$user->two_factor_secret) {
            return back()->withErrors([
                'code' => '2FA setup not initiated. Please start the setup process first.',
            ]);
        }

        $secret = decrypt($user->two_factor_secret);

        // Verify the code
        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return back()->withErrors([
                'code' => 'The provided code is invalid.',
            ]);
        }

        // Enable 2FA
        $user->update([
            'two_factor_enabled' => true,
        ]);

        return back()->with('status', '2FA has been enabled successfully!');
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'The provided password is incorrect.',
            ]);
        }

        // Disable 2FA and clear secrets
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);

        return back()->with('status', '2FA has been disabled.');
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);

        if (!$user->two_factor_enabled || !$user->two_factor_secret) {
            return back()->withErrors([
                'code' => '2FA is not enabled for this account.',
            ]);
        }

        $secret = decrypt($user->two_factor_secret);

        // Check if it's a recovery code
        if ($this->isRecoveryCode($user, $request->code)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            
            return redirect()->intended(route('dashboard'));
        }

        // Verify TOTP code
        $valid = $this->google2fa->verifyKey($secret, $request->code, 2); // 2 window tolerance

        if (!$valid) {
            return back()->withErrors([
                'code' => 'The provided code is invalid or has expired.',
            ])->withInput();
        }

        // Log the user in
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Show 2FA verification page
     */
    public function show(Request $request)
    {
        if (!$request->session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge', [
            'user_id' => $request->session()->get('2fa:user:id'),
            'remember' => $request->session()->get('2fa:remember', false),
        ]);
    }

    /**
     * Generate recovery codes
     */
    protected function generateRecoveryCodes()
    {
        $codes = [];
        
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::random(10) . '-' . Str::random(10);
        }

        return $codes;
    }

    /**
     * Check if code is a valid recovery code
     */
    protected function isRecoveryCode($user, $code)
    {
        if (!$user->two_factor_recovery_codes) {
            return false;
        }

        $recoveryCodes = $user->two_factor_recovery_codes;
        $index = array_search($code, $recoveryCodes);

        if ($index !== false) {
            // Remove used recovery code
            unset($recoveryCodes[$index]);
            $user->update([
                'two_factor_recovery_codes' => array_values($recoveryCodes),
            ]);
            
            return true;
        }

        return false;
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided password is incorrect.',
                'errors' => [
                    'password' => ['The provided password is incorrect.']
                ]
            ], 422);
        }

        if (!$user->two_factor_enabled) {
            return response()->json([
                'success' => false,
                'message' => '2FA is not enabled.',
                'errors' => [
                    'error' => ['2FA is not enabled.']
                ]
            ], 422);
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_recovery_codes' => $recoveryCodes,
        ]);

        return response()->json([
            'recovery_codes' => $recoveryCodes,
        ]);
    }
}
