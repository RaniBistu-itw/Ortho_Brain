<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>OrthoBrain Login</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
                html { font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
            </style>
        @endif
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            vuexy: {
                                primary: '#5bc0de',
                                hover: '#46b8da',
                                text: '#6e6b7b',
                                heading: '#5e5873',
                                border: '#d8d6de',
                                bg: '#f8f8f8'
                            }
                        }
                    }
                }
            }
        </script>
    </head>
    <body class="h-screen overflow-hidden bg-[#f8f8f8] text-[#6e6b7b] flex flex-col items-center justify-center">

        <div class="w-full max-w-[440px] mx-auto px-4 flex flex-col gap-3">

            <!-- Login Card -->
            <div class="bg-white rounded-[0.5rem] px-8 py-6 shadow-[0_4px_24px_0_rgba(34,41,47,0.1)] text-center">

                <!-- Logo -->
                <div class="mb-5 flex flex-col items-center">
                    <svg width="52" height="44" viewBox="0 0 64 64" fill="none" stroke="#b8b8b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="mb-[-6px]">
                        <path d="M32 12c-3.5 0-5.5 2-5.5 4 0 2-2 4-4.5 4-4.5 0-7 3-7 7.5 0 2.5-2 4-3 6-1.5 3.5 1 7 4 7 1 0 2 1 2 2.5 0 3.5 3.5 5.5 6.5 5.5 2 0 3-1.5 4.5-3 2-2 5.5-2 7.5 0 1.5 1.5 2.5 3 4.5 3 3 0 6.5-2 6.5-5.5 0-1.5 1-2.5 2-2.5 3 0 5.5-3.5 4-7-1-2-3-3.5-3-6 0-4.5-2.5-7.5-7-7.5-2.5 0-4.5-2-4.5-4 0-2-2-4-5.5-4z" fill="#ffffff" />
                        <path d="M32 16v18M23 26c2 1 2 5 0 7M41 26c-2 1-2 5 0 7M28 20c1.5 1.5 1.5 4 0 5M36 20c-1.5 1.5-1.5 4 0 5M19 33c2.5 1 3.5 4 1 6M45 33c-2.5 1-3.5 4-1 6" stroke="#b8b8b8" />
                        <path d="M30 46 l-4 8 h6 l-2 6 8-10 h-6 z" fill="#b8b8b8" stroke="none" />
                    </svg>
                    <div class="text-[2rem] font-medium tracking-tight mt-1.5 leading-none flex items-start">
                        <span class="text-[#5bc0de]">ortho</span><span class="text-[#8cc63f]">brain</span><span class="text-[#8cc63f] text-[0.7rem] ml-[1px] mt-2">™</span>
                    </div>
                    <div class="text-[11px] italic text-[#6e6b7b] mt-0.5 tracking-wide">Orthodontics for Your Dental Practice</div>
                </div>

                <!-- Welcome text -->
                <div class="text-left mb-4">
                    <h1 class="text-[1.3rem] font-medium text-[#5e5873] mb-0.5 leading-tight">Welcome to Orthobrain!</h1>
                    <p class="text-[0.85rem] text-[#6e6b7b]">Please sign-in to your account</p>
                </div>

                <!-- Form -->
                <form action="{{ url('/login') }}" method="POST" class="space-y-3 text-left" novalidate>
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-[0.82rem] font-medium text-[#5e5873] mb-1">Email</label>
                        <div class="relative flex items-center border @error('email') border-red-500 @else border-[#d8d6de] @enderror rounded-[0.358rem] bg-white transition-all overflow-hidden focus-within:border-vuexy-primary focus-within:shadow-[0_0_0_0.2rem_rgba(91,192,222,0.25)]">
                            <span class="pl-3 pr-2 py-[0.4rem] text-[#b9b9c3] flex items-center border-r @error('email') border-red-500 @else border-[#d8d6de] @enderror bg-[#f8f8f8]">
                                <svg class="h-[1.1rem] w-[1.1rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" class="flex-1 px-3 py-[0.42rem] bg-transparent outline-none text-[0.9rem] text-[#6e6b7b] placeholder-[#b9b9c3]" placeholder="Enter your email" />
                        </div>
                        @error('email')
                            <p class="text-red-500 text-[0.78rem] mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="password" class="block text-[0.82rem] font-medium text-[#5e5873]">Password</label>
                            <a href="{{ url('/forgot-password') }}" class="text-[0.82rem] text-vuexy-primary hover:text-vuexy-hover">Forgot Password?</a>
                        </div>
                        <div class="relative flex items-center border @error('password') border-red-500 @else border-[#d8d6de] @enderror rounded-[0.358rem] bg-white transition-all overflow-hidden focus-within:border-vuexy-primary focus-within:shadow-[0_0_0_0.2rem_rgba(91,192,222,0.25)]">
                            <span class="pl-3 pr-2 py-[0.4rem] text-[#b9b9c3] flex items-center border-r @error('password') border-red-500 @else border-[#d8d6de] @enderror bg-[#f8f8f8]">
                                <svg class="h-[1.1rem] w-[1.1rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </span>
                            <input id="password" name="password" type="password" class="flex-1 px-3 py-[0.42rem] bg-transparent outline-none text-[0.9rem] text-[#6e6b7b] placeholder-[#b9b9c3]" placeholder="············" />
                            <span class="px-3 cursor-pointer text-[#b9b9c3] hover:text-[#6e6b7b]" onclick="const p=document.getElementById('password');p.type=p.type==='password'?'text':'password';">
                                <svg class="h-[1.1rem] w-[1.1rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            </span>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-[0.78rem] mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="rounded border-[#d8d6de] text-vuexy-primary w-[0.95rem] h-[0.95rem] cursor-pointer" />
                            <span class="ml-2 text-[0.85rem] text-[#6e6b7b]">Remember Me</span>
                        </label>
                    </div>

                    <!-- Sign In Button -->
                    <div class="pt-1">
                        <button type="submit" class="w-full bg-vuexy-primary hover:bg-vuexy-hover text-white font-medium rounded-[0.358rem] py-[0.55rem] transition-colors shadow-[0_2px_4px_rgba(91,192,222,0.4)] text-[0.95rem]">
                            Sign in
                        </button>
                    </div>
                </form>

                <!-- Card Footer -->
                <div class="mt-4 text-center text-[0.85rem]">
                    <p class="text-[#6e6b7b] mb-0.5">Request to become a provider at no cost</p>
                    <a href="{{ url('/register') }}" class="text-vuexy-primary hover:text-vuexy-hover font-medium">Create an account</a>
                    <div class="mt-2 text-[0.8rem] text-[#5e5873] font-bold italic">Designed for OrthoDentists™</div>
                </div>
            </div>

            <!-- Page Footer -->
            <div class="text-center text-[9.5px] text-[#b9b9c3] leading-relaxed px-2">
                <p>Copyright © 2025 orthobrain®. All rights reserved. Users are subject to the <a href="#" class="text-vuexy-primary hover:underline">Agreement for Use</a>, <a href="#" class="text-vuexy-primary hover:underline">User Content Policy</a>, and <a href="#" class="text-vuexy-primary hover:underline">Independent Contractor Agreement</a>.</p>
            </div>

        </div>
    </body>
</html>
