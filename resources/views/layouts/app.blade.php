<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Chat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #f5f1eb 0%, #ede4d3 100%);
        }
        .chat-bubble-sent {
            background: linear-gradient(135deg, #d4c5a9 0%, #c9b896 100%);
        }
        .chat-bubble-received {
            background: linear-gradient(135deg, #f8f6f0 0%, #ede8dc 100%);
        }
        .btn-primary {
            background: linear-gradient(135deg, #b8a082 0%, #a08968 100%);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #a08968 0%, #8b7355 100%);
        }
        .card-shadow {
            box-shadow: 0 10px 25px rgba(184, 160, 130, 0.15);
        }
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(184, 160, 130, 0.2);
            border-color: #b8a082;
        }
        .navbar-gradient {
            background: linear-gradient(135deg, #b8a082 0%, #a08968 100%);
        }
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(248, 246, 240, 0.8);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    <!-- Navbar -->
    <nav class="navbar-gradient shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <a href="{{ route('messages.index') }}" class="text-white font-bold text-xl ml-1 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                    Secure Chat
                </a>
                @auth
                    <div class="flex items-center space-x-4 mr-2">
                        <div class="flex items-center bg-white bg-opacity-20 rounded-full px-4 py-2">
                            <span class="text-black font-medium">{{ Auth::user()->name }}</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-white hover:text-gray-200 transition-colors duration-200 flex items-center">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6">
        @if (session('success'))
            <div class="glass-effect border border-green-300 text-green-700 px-6 py-4 rounded-xl mb-6 card-shadow">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="glass-effect border border-red-300 text-red-700 px-6 py-4 rounded-xl mb-6 card-shadow">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
