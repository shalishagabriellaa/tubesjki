@extends('layouts.app')

@section('content')

<div class="flex h-screen max-h-screen">
    <!-- Sidebar - Daftar Kontak -->
    <div class="w-1/3 glass-effect rounded-l-2xl card-shadow mr-1 flex flex-col">
        <!-- Header Sidebar -->
        <div class="px-6 py-4 border-b border-gray-200 border-opacity-50">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                Konversasi
            </h2>
            <p class="text-sm text-gray-600 mt-1">{{ $users->count() }} kontak tersedia</p>
        </div>

        <!-- Daftar Kontak -->
        <div class="flex-1 overflow-y-auto">
           @foreach ($users as $user)
    @php
        $lastMessage = $messages->where(function($message) use ($user) {
            return ($message->sender_id == Auth::id() && $message->receiver_id == $user->id) ||
                   ($message->sender_id == $user->id && $message->receiver_id == Auth::id());
        })->sortByDesc('created_at')->first();
    @endphp
    <div class="px-6 py-4 hover:bg-white hover:bg-opacity-30 cursor-pointer border-b border-gray-100 border-opacity-30 transition-all duration-200" 
         onclick="selectContact({{ $user->id }}, '{{ $user->name }}')">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-200 to-amber-300 rounded-full flex items-center justify-center mr-4">
                <span class="text-amber-800 font-bold text-lg">{{ substr($user->name, 0, 1) }}</span>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-800">{{ $user->name }}</h3>
                @if($lastMessage)
                    <p class="text-sm text-gray-600 truncate">
                        {{ $lastMessage->sender_id == Auth::id() ? 'Anda: ' : '' }}
                        @if($lastMessage->message_type == 'text')
                            {{ Str::limit($lastMessage->content, 30) }}
                        @elseif($lastMessage->message_type == 'image')
                            [Gambar]
                        @elseif($lastMessage->message_type == 'audio')
                            [Audio]
                        @elseif($lastMessage->message_type == 'video')
                            [Video]
                        @else
                            [File: {{ Str::limit($lastMessage->metadata['file_name'] ?? 'File', 20) }}]
                        @endif
                    </p>
                    <p class="text-xs text-gray-500">{{ $lastMessage->created_at->diffForHumans() }}</p>
                @else
                    <p class="text-sm text-gray-500 italic">Belum ada pesan</p>
                @endif
            </div>
            <div class="w-3 h-3 bg-green-400 rounded-full"></div>
        </div>
    </div>
@endforeach
        </div>
    </div>

    <!-- Area Chat -->
    <div class="w-2/3 glass-effect rounded-r-2xl card-shadow ml-1 flex flex-col">
        <!-- Header Chat -->
        <div class="px-6 py-4 border-b border-gray-200 border-opacity-50" id="chat-header" style="display: none;">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-amber-200 to-amber-300 rounded-full flex items-center justify-center mr-3">
                        <span class="text-amber-800 font-bold" id="chat-avatar"></span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800" id="chat-name"></h3>
                        <p class="text-sm text-green-600 flex items-center">
                            <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                            Online
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    
                </div>
            </div>
        </div>

        <!-- Area Pesan -->
        <div class="flex-1 overflow-y-auto px-6 py-4" id="messages-area">
            <div class="flex items-center justify-center h-full" id="welcome-message">
                <div class="text-center">
                    <div class="w-20 h-20 bg-gradient-to-br from-amber-100 to-amber-200 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Selamat Datang di Secure Chat</h3>
                    <p class="text-gray-600">Pilih kontak dari sidebar untuk memulai percakapan</p>
                </div>
            </div>
            <div id="chat-messages" style="display: none;"></div>
        </div>

        <!-- Form Kirim Pesan -->
<div class="px-6 py-4 border-t border-gray-200 border-opacity-50" id="message-form" style="display: none;">
    <form method="POST" action="{{ route('messages.store') }}" class="flex items-center space-x-4" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="receiver_id" id="receiver_id">
        
        <!-- Attach File Button -->
        <div class="relative">
            <input type="file" name="file" id="file-input" class="hidden" accept="image/*,audio/*,video/*,.pdf,.doc,.docx"
                   onchange="handleFileSelect(event)">
            <button type="button" class="p-3 text-gray-600 hover:text-gray-800 hover:bg-white hover:bg-opacity-50 rounded-full transition-all duration-200"
                    onclick="document.getElementById('file-input').click()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
            </button>
        </div>
        
        <!-- Emoji Button -->
        <button type="button" class="p-3 text-gray-600 hover:text-gray-800 hover:bg-white hover:bg-opacity-50 rounded-full transition-all duration-200"
                onclick="toggleEmojiPicker()">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.01M15 10h1.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </button>
        
        <div class="flex-1 relative">
            <textarea name="content" id="content" rows="1" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-full focus:outline-none input-focus transition-all duration-200 bg-white bg-opacity-60 resize-none" 
                      placeholder="Ketik pesan Anda..." 
                      onkeypress="handleKeyPress(event)"></textarea>
            <!-- Emoji Picker (Hidden by Default) -->
            <div id="emoji-picker" class="absolute bottom-14 right-0 bg-white rounded-lg shadow-lg p-2 hidden max-h-64 overflow-y-auto">
                <!-- Emoji list will be populated by JavaScript -->
            </div>
        </div>
        
        <button type="submit" class="btn-primary text-white p-3 rounded-full hover:shadow-lg transform hover:scale-105 transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
        </button>
    </form>
</div>

    </div>
</div>

<script>
let selectedUserId = null;
let selectedUserName = '';
let allMessages = @json($messages);

function selectContact(userId, userName) {
    selectedUserId = userId;
    selectedUserName = userName;
    
    // Update form
    document.getElementById('receiver_id').value = userId;
    
    // Update header
    document.getElementById('chat-name').textContent = userName;
    document.getElementById('chat-avatar').textContent = userName.charAt(0);
    
    // Show/hide elements
    document.getElementById('welcome-message').style.display = 'none';
    document.getElementById('chat-header').style.display = 'block';
    document.getElementById('message-form').style.display = 'block';
    document.getElementById('chat-messages').style.display = 'block';
    
    // Load messages
    loadMessages(userId);
    
    // Focus input
    document.getElementById('content').focus();
}

// File handling
function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        const fileName = file.name;
        const contentTextarea = document.getElementById('content');
        // Optionally, show file name in textarea or UI
        contentTextarea.value = `Mengirim file: ${fileName}`;
        // You can add a preview for images if needed
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('img');
                preview.src = e.target.result;
                preview.className = 'max-w-xs my-2';
                document.getElementById('chat-messages').appendChild(preview);
            };
            reader.readAsDataURL(file);
        }
    }
}

// Update loadMessages to handle file messages
function loadMessages(userId) {
    const messagesContainer = document.getElementById('chat-messages');
    const currentUserId = {{ Auth::id() }};
    
    const chatMessages = allMessages.filter(message => 
        (message.sender_id === currentUserId && message.receiver_id === userId) ||
        (message.sender_id === userId && message.receiver_id === currentUserId)
    );
    
    chatMessages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    
    let messagesHtml = '';
    
    if (chatMessages.length === 0) {
        messagesHtml = `
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gradient-to-br from-amber-100 to-amber-200 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <p class="text-gray-600">Belum ada pesan dengan ${selectedUserName}</p>
                <p class="text-gray-500 text-sm mt-1">Mulai percakapan dengan mengirim pesan pertama</p>
            </div>
        `;
    } else {
        chatMessages.forEach((message, index) => {
            const isFromMe = message.sender_id === currentUserId;
            const messageDate = new Date(message.created_at);
            const formattedTime = messageDate.toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            const showDateSeparator = index === 0 || 
                new Date(chatMessages[index - 1].created_at).toDateString() !== messageDate.toDateString();
            
            if (showDateSeparator) {
                const formattedDate = messageDate.toLocaleDateString('id-ID', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                messagesHtml += `
                    <div class="text-center py-4">
                        <span class="bg-white bg-opacity-70 px-4 py-2 rounded-full text-sm text-gray-600 font-medium">${formattedDate}</span>
                    </div>
                `;
            }
            
            // Handle different message types
            let contentHtml = '';
            if (message.message_type === 'text') {
                contentHtml = `<p class="break-words">${message.content}</p>`;
            } else if (['image', 'file', 'audio', 'video'].includes(message.message_type)) {
                const fileUrl = message.metadata?.file_path ? `/storage/${message.metadata.file_path}` : '#';
                const fileName = message.metadata?.file_name || 'File';
                if (message.message_type === 'image') {
                    contentHtml = `<a href="${fileUrl}" target="_blank"><img src="${fileUrl}" class="max-w-xs rounded-lg" alt="${fileName}"></a>`;
                } else if (message.message_type === 'audio') {
                    contentHtml = `<audio controls><source src="${fileUrl}" type="${message.metadata?.mime_type}"></audio>`;
                } else if (message.message_type === 'video') {
                    contentHtml = `<video controls class="max-w-xs"><source src="${fileUrl}" type="${message.metadata?.mime_type}"></video>`;
                } else {
                    contentHtml = `<a href="${fileUrl}" class="text-blue-500 underline" download>${fileName}</a>`;
                }
            }
            
            messagesHtml += `
                <div class="flex ${isFromMe ? 'justify-end' : 'justify-start'} mb-4">
                    <div class="max-w-xs lg:max-w-md px-4 py-3 rounded-2xl ${isFromMe ? 'chat-bubble-sent text-gray-800' : 'chat-bubble-received text-gray-800'} shadow-sm">
                        ${contentHtml}
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs text-gray-600">${formattedTime}</span>
                            ${isFromMe ? `
                                <div class="flex items-center ml-2">
                                    <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
    }
    
    messagesContainer.innerHTML = messagesHtml;
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function toggleEmojiPicker() {
    const pickerContainer = document.getElementById('emoji-picker');
    pickerContainer.classList.toggle('hidden');
    
    if (!pickerContainer.innerHTML) {
        const picker = new Picker({
            data,
            onEmojiSelect: (emoji) => {
                document.getElementById('content').value += emoji.native;
                pickerContainer.classList.add('hidden');
                document.getElementById('content').focus();
                const event = new Event('input');
                document.getElementById('content').dispatchEvent(event);
            }
        });
        pickerContainer.appendChild(picker);
    }
}

// Close emoji picker when clicking outside
document.addEventListener('click', function(event) {
    const picker = document.getElementById('emoji-picker');
    const emojiButton = document.querySelector('button[onclick="toggleEmojiPicker()"]');
    if (!picker.contains(event.target) && !emojiButton.contains(event.target)) {
        picker.classList.add('hidden');
    }
});

function handleKeyPress(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        event.target.closest('form').submit();
    }
}

// Auto-resize textarea
document.getElementById('content').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

// Real-time message refresh (optional - you can implement with WebSocket or polling)
setInterval(() => {
    if (selectedUserId) {
        // You can implement AJAX call here to refresh messages
        // For now, we'll just reload the page data
    }
}, 30000); // Refresh every 30 seconds
</script>

@if(session('success'))
<script>
    // Show success message and refresh if needed
    setTimeout(() => {
        if (selectedUserId) {
            loadMessages(selectedUserId);
        }
    }, 100);
</script>
@endif
@endsection
