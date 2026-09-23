<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EpbxCallController extends Controller
{
    /**
     * Trigger an automated AI voice order verification call via ePBX API.
     */
    public function makeVerificationCall(Request $request, Sale $sale)
    {
        $phone = trim($request->input('phone', $sale->customer?->phone ?? ''));
        
        // Sanitize phone number (e.g., strip spaces, dashes, +88)
        $cleanPhone = preg_replace('/[^\d]/', '', $phone);
        if (str_starts_with($cleanPhone, '880') && strlen($cleanPhone) === 13) {
            $cleanPhone = '0' . substr($cleanPhone, 3);
        }

        if (empty($cleanPhone) || strlen($cleanPhone) < 10) {
            return response()->json([
                'success' => false,
                'message' => 'গ্রাহকের সঠিক ফোন নম্বর পাওয়া যায়নি (' . ($phone ?: 'খালি') . ')।',
            ], 422);
        }

        $customerName = $sale->customer?->name ?? 'Customer';
        $orderId      = (string) $sale->invoice_no;
        $amount       = (string) round($sale->total ?? 0);
        $webhookUrl   = url('/api/epbx/webhook');

        $token      = config('services.epbx.api_token', 'SmjIPNlkdCDZpkXDLJEL7WoASn7ib5xPhhihFvSV');
        $verifyUrl  = config('services.epbx.verify_url', 'https://ebsbazarbd.epbx.bd/api/v1/calls/verify');
        $epbxHost   = parse_url(config('services.epbx.base_url', 'https://ebsbazarbd.epbx.bd'), PHP_URL_HOST) ?? 'ebsbazarbd.epbx.bd';

        try {
            $response = Http::withHeaders([
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->withoutVerifying()->timeout(15)->post($verifyUrl, [
                'phone_number'  => $cleanPhone,
                'customer_name' => $customerName,
                'order_id'      => $orderId,
                'amount'        => $amount,
                'webhook_url'   => $webhookUrl,
            ]);

            $resData = $response->json() ?? [];

            // 1. Success
            if ($response->successful()) {
                // Record note in sale
                $userName = auth()->user()?->name ?? 'Admin';
                $sale->notes()->create([
                    'user_id' => auth()->id(),
                    'note'    => "🤖 [ePBX AI Call] Order verification call initiated to {$cleanPhone} by {$userName}. Amount: ৳{$amount}",
                ]);

                return response()->json([
                    'success' => true,
                    'status'  => 'initiated',
                    'message' => 'ePBX AI Verification Call সফলভাবে পাঠানো হয়েছে! গ্রাহকের ফোনে কল যাচ্ছে...',
                    'data'    => $resData,
                ]);
            }

            // 2. Insufficient Balance (402 Payment Required)
            if ($response->status() === 402) {
                $reqFee = $resData['required_fee'] ?? null;
                $rawMsg = $resData['error'] ?? 'Insufficient Wallet Balance';

                return response()->json([
                    'success'      => false,
                    'error_type'   => 'insufficient_balance',
                    'required_fee' => $reqFee,
                    'message'      => 'ePBX ওয়ালেটে ব্যালেন্স অপর্যাপ্ত' . ($reqFee ? " (প্রয়োজন ৳{$reqFee} BDT)" : "") . "। কল পাঠাতে https://{$epbxHost} থেকে ওয়ালেট রিচার্জ করুন।",
                    'raw_error'    => $rawMsg,
                ], 402);
            }

            // 3. AI Text-to-Speech service unavailable (503)
            $rawMsg = $resData['error'] ?? $resData['message'] ?? '';
            if ($response->status() === 503 || str_contains(strtolower($rawMsg), 'text-to-speech')) {
                return response()->json([
                    'success'    => false,
                    'error_type' => 'tts_unavailable',
                    'message'    => 'ePBX সার্ভারের ElevenLabs AI Voice (TTS) সার্ভিস এই মুহূর্তে আনঅ্যাভেইলেবল। টাকা আপনার ePBX ওয়ালেটে রিফান্ড করা হয়েছে। সরাসরি কথা বলতে উপরের "ePBX Web Dialer" অথবা "MicroSIP" ব্যবহার করুন।',
                    'raw_error'  => $rawMsg,
                    'data'       => $resData,
                ], 503);
            }

            // 4. Validation or other errors (422, etc.)
            $errMsg = $resData['message'] ?? $resData['error'] ?? 'ePBX সার্ভার থেকে কোনো সাড়া পাওয়া যায়নি (Status: ' . $response->status() . ')';
            if (isset($resData['errors']) && is_array($resData['errors'])) {
                $flattened = [];
                foreach ($resData['errors'] as $errs) {
                    $flattened = array_merge($flattened, (array)$errs);
                }
                $errMsg .= ' (' . implode(', ', $flattened) . ')';
            }

            return response()->json([
                'success' => false,
                'message' => $errMsg,
                'data'    => $resData,
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('ePBX Verification Call Exception', [
                'sale_id' => $sale->id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'ePBX সার্ভারের সাথে সংযোগ স্থাপন করা যায়নি: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save a quick call disposition note from the IP call modal.
     */
    public function saveCallNote(Request $request, Sale $sale)
    {
        $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        $noteText = trim($request->input('note'));
        $note = $sale->notes()->create([
            'user_id' => auth()->id(),
            'note'    => $noteText,
        ]);

        $note->load('user.roles');
        $user = $note->user;
        $role = $user ? ($user->roles->first()?->name ?? 'Staff') : 'Staff';

        return response()->json([
            'success' => true,
            'message' => 'কল নোট সফলভাবে সংরক্ষণ করা হয়েছে।',
            'note'    => [
                'id'                   => $note->id,
                'note'                 => $note->note,
                'user_name'            => $user ? $user->name : 'User #' . $note->user_id,
                'user_role'            => ucfirst($role),
                'created_at_formatted' => $note->created_at->format('d M Y, h:i A'),
                'time_ago'             => $note->created_at->diffForHumans(),
            ],
            'count'   => $sale->notes()->count(),
        ]);
    }

    /**
     * Public webhook endpoint for asynchronous call completion updates from ePBX.
     */
    public function handleWebhook(Request $request)
    {
        Log::info('ePBX Call Webhook Received:', $request->all());

        $orderId = $request->input('order_id');
        $status  = $request->input('status') ?? $request->input('call_status') ?? 'completed';
        $pressed = $request->input('dtmf') ?? $request->input('pressed_key') ?? null;
        $phone   = $request->input('phone_number') ?? '';

        if ($orderId) {
            $sale = Sale::where('invoice_no', $orderId)->first();
            if ($sale) {
                $details = "📞 [ePBX Webhook] Call to {$phone}: Status '{$status}'";
                if ($pressed !== null) {
                    $details .= " | Customer pressed: {$pressed}";
                }
                $sale->notes()->create([
                    'user_id' => null,
                    'note'    => $details,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook received and processed',
        ]);
    }
}
