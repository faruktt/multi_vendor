<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminChatController extends Controller
{
    /** Admin Live Chat inbox view */
    public function index(Request $request)
    {
        return view('admin.chat.index');
    }

    /** Fetch conversation list with search and unread badges */
    public function conversations(Request $request)
    {
        $query = ChatConversation::with(['customer', 'latestMessage'])
            ->orderByDesc('last_message_at');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $conversations = $query->get()->map(function ($conv) {
            return [
                'id'                    => $conv->id,
                'customer_id'           => $conv->customer_id,
                'customer_name'         => $conv->customer->name ?? 'Customer #' . $conv->customer_id,
                'customer_phone'        => $conv->customer->phone ?? 'No phone',
                'customer_email'        => $conv->customer->email,
                'last_message'          => $conv->last_message_preview,
                'last_message_at'       => $conv->last_message_at ? $conv->last_message_at->toIso8601String() : null,
                'formatted_time'        => $conv->formatted_time,
                'admin_unread_count'    => (int) $conv->admin_unread_count,
                'status'                => $conv->status,
            ];
        });

        return response()->json([
            'conversations' => $conversations,
            'total_unread'  => (int) ChatConversation::sum('admin_unread_count'),
        ]);
    }

    /** Load messages for a specific conversation and clear unread */
    public function messages(ChatConversation $conversation)
    {
        // Mark customer messages as read
        ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_type', 'customer')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $conversation->update(['admin_unread_count' => 0]);

        $customer = $conversation->customer;
        $orderCount = 0;
        $totalSpent = 0;

        if ($customer) {
            $orderCount = Sale::withoutGlobalScopes()->where('customer_id', $customer->id)->count();
            $totalSpent = (float) Sale::withoutGlobalScopes()->where('customer_id', $customer->id)->where('order_status', '!=', 'cancelled')->sum('total');
        }

        $messages = $conversation->messages()
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'conversation' => [
                'id'                 => $conversation->id,
                'customer_id'        => $conversation->customer_id,
                'customer_name'      => $customer->name ?? 'Customer #' . $conversation->customer_id,
                'customer_phone'     => $customer->phone ?? 'No phone',
                'customer_email'     => $customer->email,
                'customer_address'   => $customer->address,
                'registered_at'      => $customer->created_at ? $customer->created_at->format('d M Y') : 'N/A',
                'order_count'        => $orderCount,
                'total_spent'        => $totalSpent,
                'status'             => $conversation->status,
            ],
            'messages' => $messages,
        ]);
    }

    /** Send an admin reply to a conversation */
    public function sendMessage(Request $request, ChatConversation $conversation)
    {
        $request->validate([
            'message' => 'nullable|string|max:3000',
            'image'   => 'nullable|image|max:5120',
        ]);

        if (empty(trim((string) $request->message)) && !$request->hasFile('image')) {
            return response()->json(['success' => false, 'message' => 'Message or image is required.'], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('chat', 'uploads');
        }

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'admin',
            'sender_id'       => Auth::id(),
            'message'         => trim((string) $request->message) ?: null,
            'image_path'      => $imagePath,
            'is_read'         => false,
        ]);

        $conversation->update([
            'last_message_at'       => now(),
            'customer_unread_count' => $conversation->customer_unread_count + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /** Poll new messages for open active chat */
    public function poll(Request $request, ChatConversation $conversation)
    {
        $lastId = (int) $request->input('last_id', 0);

        $newMessages = ChatMessage::where('conversation_id', $conversation->id)
            ->where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->get();

        if ($newMessages->where('sender_type', 'customer')->isNotEmpty()) {
            ChatMessage::where('conversation_id', $conversation->id)
                ->where('sender_type', 'customer')
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $conversation->update(['admin_unread_count' => 0]);
        }

        return response()->json([
            'messages' => $newMessages,
        ]);
    }

    /** Global unread badge for admin panel */
    public function unreadCount()
    {
        $count = (int) ChatConversation::sum('admin_unread_count');
        return response()->json(['unread_count' => $count]);
    }
}
