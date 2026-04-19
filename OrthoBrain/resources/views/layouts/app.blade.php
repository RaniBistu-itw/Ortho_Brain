<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>orthoBrain - @yield('title', 'Dashboard')</title>
    <!-- Vuexy Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700:1,400&display=swap" rel="stylesheet">

    <!-- Injection of Tailwind CDN ensures arbitrary values compile if Vite isn't active -->
    <script src="https://cdn.tailwindcss.com"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/app.css')
    @endif

    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #f8f8f8; color: #6e6b7b; }
        .top-nav { height: 62px; background: white; margin: 0; border-radius: 0; box-shadow: 0 2px 6px 0 rgba(34,41,47,.08); border-bottom: 1px solid #f0eff5; }
        input[type="radio"]:not(.sr-only), input[type="checkbox"]:not(.sr-only) { accent-color: #5bc0de; cursor: pointer; width: 1rem; height: 1rem; min-width: 1rem; flex-shrink: 0; }
    </style>
</head>
<body class="flex min-h-screen overflow-x-hidden">

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen">

        @php
            $navDoctor = \App\Models\Doctor::where('user_id', auth()->id())->first();
            $navName   = $navDoctor ? $navDoctor->first_name . ' ' . $navDoctor->last_name : auth()->user()->email;
        @endphp
        <!-- Top Navbar -->
        <div class="top-nav flex items-center justify-between px-4 z-10 transition-all">
            <div class="text-[0.95rem] font-bold italic text-[#5e5873]">
                Designed for OrthoDentists™
            </div>

            <div class="flex items-center space-x-5">

                <!-- Profile Area -->
                <div class="relative flex items-center cursor-pointer" id="profileToggle">
                    <div class="text-right mr-3 hidden sm:block">
                        <div class="text-[0.9rem] font-medium text-[#5e5873]">{{ $navName }}</div>
                    </div>
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full bg-[#f3f2f7] border border-[#d8d6de] flex items-center justify-center overflow-hidden">
                            <svg class="w-8 h-8 text-[#b9b9c3] mt-2" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                        <span class="absolute bottom-[0px] right-[1px] w-[11px] h-[11px] bg-[#28c76f] border-[1.5px] border-white rounded-full"></span>
                    </div>

                    <!-- Dropdown -->
                    <div id="profileDropdown" class="hidden absolute right-0 top-full mt-2 w-[16rem] bg-white rounded shadow-lg border border-[#ebe9f1] z-50 py-1">
                        <div class="px-4 py-3 flex items-center space-x-3 cursor-default border-b border-[#ebe9f1] mb-1">
                            <div class="relative flex-shrink-0">
                                <div class="w-[40px] h-[40px] rounded-full bg-[#f3f2f7] border border-[#d8d6de] flex items-center justify-center overflow-hidden">
                                    <svg class="w-8 h-8 text-[#b9b9c3] mt-2" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                    </svg>
                                </div>
                                <span class="absolute bottom-[2px] right-[1px] w-[11px] h-[11px] bg-[#28c76f] border-[1.5px] border-white rounded-full inset-0 m-auto mt-7 ml-7"></span>
                            </div>
                            <span class="text-[0.95rem] font-medium text-[#5e5873] leading-tight pr-2">{{ $navName }}</span>
                        </div>

                        <a href="{{ route('doctor.profile.index') }}" class="block px-4 py-2 text-[0.95rem] text-[#6e6b7b] hover:bg-[#f8f8f8] hover:text-[#5bc0de] flex items-center transition-colors">
                            <svg style="width: 18px; height: 18px;" class="mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            My Profile
                        </a>
                        <a href="{{ route('doctor.profile.settings') }}" class="block px-4 py-2 text-[0.95rem] text-[#6e6b7b] hover:bg-[#f8f8f8] hover:text-[#5bc0de] flex items-center transition-colors">
                            <svg style="width: 18px; height: 18px;" class="mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Change Password
                        </a>
                        <div class="border-t border-[#ebe9f1] my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-[0.95rem] text-[#6e6b7b] hover:bg-[#f8f8f8] hover:text-[#ea5455] flex items-center transition-colors">
                                <svg style="width: 18px; height: 18px;" class="mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inner Content Window -->
        <main class="w-full px-6 py-5 flex-1">
            @yield('content')
        </main>

    </div>

    <!-- Dropdown Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('profileToggle');
            const dropdown = document.getElementById('profileDropdown');

            toggle.addEventListener('click', function(e) {
                dropdown.classList.toggle('hidden');
                e.stopPropagation();
            });

            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>