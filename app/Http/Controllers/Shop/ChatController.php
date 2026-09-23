<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /** Initialize or retrieve customer chat conversation */
    public function init(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return response()->json(['authenticated' => false], 401);
        }

        $branch = Vendor::onlineStore();

        $conversation = ChatConversation::firstOrCreate(
            ['vendor_id' => $branch->id, 'customer_id' => $customer->id],
            ['status' => 'open', 'last_message_at' => now()]
        );

        // Mark admin messages as read
        ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $conversation->update(['customer_unread_count' => 0]);

        $messages = $conversation->messages()
            ->orderBy('id', 'asc')
            ->take(100)
            ->get();

        return response()->json([
            'authenticated'   => true,
            'conversation_id' => $conversation->id,
            'customer'        => [
                'id'    => $customer->id,
                'name'  => $customer->name,
                'phone' => $customer->phone,
            ],
            'messages'        => $messages,
        ]);
    }

    /** Customer send message (text and/or image) */
    public function sendMessage(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Please login.'], 401);
        }

        $request->validate([
            'message' => 'nullable|string|max:3000',
            'image'   => 'nullable|image|max:5120',
        ]);

        if (empty(trim((string) $request->message)) && !$request->hasFile('image')) {
            return response()->json(['success' => false, 'message' => 'Message or image is required.'], 422);
        }

        $branch = Vendor::onlineStore();

        $conversation = ChatConversation::firstOrCreate(
            ['vendor_id' => $branch->id, 'customer_id' => $customer->id],
            ['status' => 'open']
        );

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('chat', 'uploads');
        }

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'customer',
            'sender_id'       => $customer->id,
            'message'         => trim((string) $request->message) ?: null,
            'image_path'      => $imagePath,
            'is_read'         => false,
        ]);

        $conversation->update([
            'last_message_at'    => now(),
            'admin_unread_count' => $conversation->admin_unread_count + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /** Poll for new messages */
    public function poll(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return response()->json(['authenticated' => false], 401);
        }

        $branch = Vendor::onlineStore();

        $conversation = ChatConversation::where('vendor_id', $branch->id)
            ->where('customer_id', $customer->id)
            ->first();

        if (!$conversation) {
            return response()->json(['messages' => [], 'unread_count' => 0]);
        }

        $lastId = (int) $request->input('last_id', 0);

        $newMessages = ChatMessage::where('conversation_id', $conversation->id)
            ->where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->get();

        // If chat widget is open (is_open parameter), mark received admin messages as read
        if ($request->boolean('is_open') && $newMessages->where('sender_type', 'admin')->isNotEmpty()) {
            ChatMessage::where('conversation_id', $conversation->id)
                ->where('sender_type', 'admin')
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $conversation->update(['customer_unread_count' => 0]);
        }

        return response()->json([
            'authenticated' => true,
            'messages'      => $newMessages,
            'unread_count'  => $conversation->customer_unread_count,
        ]);
    }
}
