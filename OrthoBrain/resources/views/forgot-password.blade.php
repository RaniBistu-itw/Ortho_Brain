<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Forgot Password - OrthoBrain</title>
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
                                bg: '#f8f8f8',
                                success: '#28c76f'
                            }
                        }
                    }
                }
            }
        </script>
    </head>
    <body class="min-h-screen bg-[#f8f8f8] text-[#6e6b7b] flex flex-col pt-[10vh]">
        
        <div class="w-full max-w-[450px] mx-auto px-4">
            
            <div class="bg-white rounded-lg px-8 py-10 shadow-[0_4px_24px_0_rgba(34,41,47,0.1)] mb-6 text-center">
                <!-- Logo area -->
                <div class="mb-10 flex flex-col items-center justify-center">
                    <div class="flex flex-col items-center">
                        <svg width="60" height="52" viewBox="0 0 64 64" fill="none" stroke="#b8b8b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="mb-[-8px]">
                            <!-- Brain Shape -->
                            <path d="M32 12c-3.5 0-5.5 2-5.5 4 0 2-2 4-4.5 4-4.5 0-7 3-7 7.5 0 2.5-2 4-3 6-1.5 3.5 1 7 4 7 1 0 2 1 2 2.5 0 3.5 3.5 5.5 6.5 5.5 2 0 3-1.5 4.5-3 2-2 5.5-2 7.5 0 1.5 1.5 2.5 3 4.5 3 3 0 6.5-2 6.5-5.5 0-1.5 1-2.5 2-2.5 3 0 5.5-3.5 4-7-1-2-3-3.5-3-6 0-4.5-2.5-7.5-7-7.5-2.5 0-4.5-2-4.5-4 0-2-2-4-5.5-4z" fill="#ffffff" />
                            <!-- Internal Brain Folds -->
                            <path d="M32 16v18M23 26c2 1 2 5 0 7M41 26c-2 1-2 5 0 7M28 20c1.5 1.5 1.5 4 0 5M36 20c-1.5 1.5-1.5 4 0 5M19 33c2.5 1 3.5 4 1 6M45 33c-2.5 1-3.5 4-1 6" stroke="#b8b8b8" />
                            <!-- Lightning Bolt -->
                            <path d="M30 46 l-4 8 h6 l-2 6 8-10 h-6 z" fill="#b8b8b8" stroke="none" />
                        </svg>
                        <div class="text-[2.2rem] font-medium tracking-tight mt-2 leading-[1.1] flex items-start">
                            <span class="text-[#5bc0de]">ortho</span><span class="text-[#8cc63f]">brain</span><span class="text-[#8cc63f] text-[0.8rem] ml-[1px] mt-2">™</span>
                        </div>
                        <div class="text-[12px] italic text-[#6e6b7b] mt-1 tracking-wide" style="font-family: inherit;">
                            Orthodontics for Your Dental Practice
                        </div>
                    </div>
                </div>

                <div class="text-left mb-6">
                    <h1 class="text-2xl font-medium text-[#5e5873] mb-2 leading-tight">Forgot Password? 🔒</h1>
                    <p class="text-[0.9rem] text-[#6e6b7b]">Enter your email and we'll send you link to reset your password.</p>
                </div>

                <form action="{{ url('/forgot-password') }}" method="POST" class="space-y-4 text-left">
                    @csrf
                    <!-- Email Group -->
                    <div>
                        <label for="email" class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Email ID</label>
                        <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] bg-white transition-all overflow-hidden focus-within:border-vuexy-primary focus-within:shadow-[0_0_0_0.2rem_rgba(91,192,222,0.25)]">
                            <span class="pl-3 pr-2 py-2 text-[#b9b9c3] flex items-center justify-center border-r border-[#d8d6de] bg-[#f8f8f8]">
                                <svg class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </span>
                            <input id="email" name="email" type="email" required class="flex-1 px-3 py-[0.5rem] bg-transparent outline-none text-[0.95rem] text-[#6e6b7b] placeholder-[#b9b9c3]" placeholder="Enter your email" />
                        </div>
                    </div>

                    @if($status ?? false)
                        <div class="text-[#28c76f] text-[0.9rem] mt-2 mb-2">
                            We have emailed your password reset link.
                        </div>
                    @endif

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-vuexy-primary hover:bg-vuexy-hover text-white font-medium rounded-[0.358rem] py-[0.6rem] transition-colors shadow-[0_2px_4px_rgba(91,192,222,0.4)]">
                            Send Reset Link
                        </button>
                    </div>
                </form>

                <!-- Back to login link -->
                <div class="mt-4 text-center">
                    <a href="{{ url('/login') }}" class="text-vuexy-primary hover:text-vuexy-hover text-[0.9rem] flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to login
                    </a>
                </div>
            </div>
            
        </div>
    </body>
</html>
