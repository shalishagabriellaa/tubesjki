<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index()
{
    $users = User::where('id', '!=', Auth::id())
        ->withCount(['receivedMessages as unread_count' => function ($query) {
            $query->where('sender_id', Auth::id())
                  ->where('is_read', false)
                  ->where('deleted_by_receiver', false); // Tambahkan ini
        }])
        ->get();

    $messages = Message::where('receiver_id', Auth::id())
        ->orWhere('sender_id', Auth::id())
        ->with(['sender', 'receiver'])
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($message) {
            try {
                $message->content = Crypt::decryptString($message->content);
            } catch (\Exception $e) {
                $message->content = '[Pesan tidak dapat didekripsi]';
            }
            return $message;
        });

    // Mark messages as read when viewing
    Message::where('receiver_id', Auth::id())
        ->where('is_read', false)
        ->update(['is_read' => true, 'read_at' => now()]);

    return view('messages.index', compact('users', 'messages'));
}

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id|different:' . Auth::id(),
            'content' => 'required|string|max:1000',
        ], [
            'receiver_id.required' => 'Pilih penerima pesan.',
            'receiver_id.exists' => 'Penerima tidak ditemukan.',
            'receiver_id.different' => 'Anda tidak bisa mengirim pesan ke diri sendiri.',
            'content.required' => 'Pesan tidak boleh kosong.',
            'content.max' => 'Pesan terlalu panjang (maksimal 1000 karakter).',
        ]);

        try {
            $message = Message::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $request->receiver_id,
                'content' => Crypt::encryptString($request->content),
                'is_read' => false,
                'sent_at' => now(),
            ]);

            // Log activity
            \Log::info('Message sent', [
                'sender_id' => Auth::id(),
                'receiver_id' => $request->receiver_id,
                'message_id' => $message->id,
                'timestamp' => now()
            ]);

            return redirect()->route('messages.index')->with('success', 'Pesan berhasil dikirim!');
        } catch (\Exception $e) {
            \Log::error('Failed to send message', [
                'error' => $e->getMessage(),
                'sender_id' => Auth::id(),
                'receiver_id' => $request->receiver_id
            ]);

            return redirect()->route('messages.index')->with('error', 'Gagal mengirim pesan. Silakan coba lagi.');
        }
    }

    public function getMessages(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $messages = Message::where(function ($query) use ($request) {
            $query->where('sender_id', Auth::id())
                  ->where('receiver_id', $request->user_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('sender_id', $request->user_id)
                  ->where('receiver_id', Auth::id());
        })
        ->with(['sender', 'receiver'])
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($message) {
            try {
                $message->content = Crypt::decryptString($message->content);
            } catch (\Exception $e) {
                $message->content = '[Pesan tidak dapat didekripsi]';
            }
            return $message;
        });

        // Mark messages as read
        Message::where('sender_id', $request->user_id)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:messages,id'
        ]);

        $message = Message::findOrFail($request->message_id);
        
        // Only allow marking as read if user is the receiver
        if ($message->receiver_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    public function getUnreadCount()
    {
        $count = Message::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function deleteMessage(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:messages,id'
        ]);

        $message = Message::findOrFail($request->message_id);
        
        // Only allow deletion if user is sender or receiver
        if ($message->sender_id !== Auth::id() && $message->receiver_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Soft delete - mark as deleted for this user
        if ($message->sender_id === Auth::id()) {
            $message->update(['deleted_by_sender' => true]);
        } else {
            $message->update(['deleted_by_receiver' => true]);
        }

        // If both users deleted, actually delete the message
        if ($message->deleted_by_sender && $message->deleted_by_receiver) {
            $message->delete();
        }

        return response()->json(['success' => true]);
    }

    public function searchMessages(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:3',
            'user_id' => 'nullable|exists:users,id'
        ]);

        $query = $request->input('query');
        $userId = $request->input('user_id');

        $messages = Message::where(function ($q) use ($userId) {
            if ($userId) {
                $q->where(function ($subQuery) use ($userId) {
                    $subQuery->where('sender_id', Auth::id())
                             ->where('receiver_id', $userId);
                })->orWhere(function ($subQuery) use ($userId) {
                    $subQuery->where('sender_id', $userId)
                             ->where('receiver_id', Auth::id());
                });
            } else {
                $q->where('sender_id', Auth::id())
                  ->orWhere('receiver_id', Auth::id());
            }
        })
        ->with(['sender', 'receiver'])
        ->get()
        ->filter(function ($message) use ($query) {
            try {
                $decrypted = Crypt::decryptString($message->content);
                return stripos($decrypted, $query) !== false;
            } catch (\Exception $e) {
                return false;
            }
        })
        ->map(function ($message) {
            try {
                $message->content = Crypt::decryptString($message->content);
            } catch (\Exception $e) {
                $message->content = '[Pesan tidak dapat didekripsi]';
            }
            return $message;
        })
        ->values();

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'count' => $messages->count()
        ]);
    }

    public function getUsersWithLastMessage()
    {
        $users = User::where('id', '!=', Auth::id())
            ->get()
            ->map(function ($user) {
                $lastMessage = Message::where(function ($query) use ($user) {
                    $query->where('sender_id', Auth::id())
                          ->where('receiver_id', $user->id);
                })->orWhere(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)
                          ->where('receiver_id', Auth::id());
                })
                ->orderBy('created_at', 'desc')
                ->first();

                if ($lastMessage) {
                    try {
                        $lastMessage->content = Crypt::decryptString($lastMessage->content);
                    } catch (\Exception $e) {
                        $lastMessage->content = '[Pesan tidak dapat didekripsi]';
                    }
                }

                $unreadCount = Message::where('sender_id', $user->id)
                    ->where('receiver_id', Auth::id())
                    ->where('is_read', false)
                    ->count();

                return [
                    'user' => $user,
                    'last_message' => $lastMessage,
                    'unread_count' => $unreadCount
                ];
            })
            ->sortByDesc(function ($item) {
                return $item['last_message'] ? $item['last_message']->created_at : '1970-01-01';
            })
            ->values();

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }
}
