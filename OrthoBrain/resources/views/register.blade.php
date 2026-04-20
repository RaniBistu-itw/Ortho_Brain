<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Orthobrain Registration</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap');
                html { font-family: 'Montserrat', ui-sans-serif, system-ui, sans-serif; }
                .vx-input-focus:focus-within { border-color: #5bc0de; box-shadow: 0 0 0 3px rgba(91, 192, 222, 0.1); }
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
                                success: '#28c76f',
                                successHover: '#23b063'
                            }
                        }
                    }
                }
            }
        </script>
        <style>
            input:-webkit-autofill,
            input:-webkit-autofill:hover, 
            input:-webkit-autofill:focus, 
            input:-webkit-autofill:active{
                -webkit-box-shadow: 0 0 0 30px #fff9e6 inset !important;
                background-color: #fff9e6 !important;
            }
            .nav-item .nav-icon { background: #f8f8f8; color: #b9b9c3; transition: all 0.2s; }
            .nav-item .nav-text { color: #5e5873; font-weight: 500; transition: all 0.2s; }
            .nav-item.active .nav-icon { background: #5bc0de; color: #fff; box-shadow: 0 2px 4px rgba(91,192,222,0.4); }
            .nav-item.active .nav-text { color: #5bc0de; }
            html { scroll-behavior: smooth; }
        </style>
    
        <style>
/* Styling for the custom toggle switches */
    input[type=checkbox].sr-only:checked ~ .dot { transform: translateX(0%); }
    input[type=checkbox].sr-only:not(:checked) ~ .bg-\\[\\#5bc0de\\] { background-color: #d8d6de; }
    input[type=checkbox].sr-only:not(:checked) ~ .dot { transform: translateX(-125%); }

    /* Gray toggles for Preferences section */
    input[type=checkbox].sr-only:checked ~ .custom-toggle-bg { background-color: #5bc0de; }
    input[type=checkbox].sr-only:not(:checked) ~ .custom-toggle-bg { background-color: #ebe9f1; }
    
    /* Vuexy Checkbox & Radio native tinting */
    input[type="radio"]:not(.sr-only),
    input[type="checkbox"]:not(.sr-only) {
        accent-color: #5bc0de;
        cursor: pointer;
        width: 1rem;
        height: 1rem;
        min-width: 1rem;
        flex-shrink: 0;
    }
    
    /* Custom thin scrollbar for Doctor Preferences */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #fcfcfc;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #d8d6de;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #b9b9c3;
    }
        </style>
    </head>
    <body class="min-h-screen bg-vuexy-bg text-[#6e6b7b] pb-10">
        <div class="px-6 py-8 sm:px-8 w-full">
            
            <header class="mb-10 flex flex-col items-center justify-center">
                <div class="flex flex-col items-center">
                    <svg width="70" height="60" viewBox="0 0 64 64" fill="none" stroke="#b8b8b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="mb-[-8px]">
                        <path d="M32 12c-3.5 0-5.5 2-5.5 4 0 2-2 4-4.5 4-4.5 0-7 3-7 7.5 0 2.5-2 4-3 6-1.5 3.5 1 7 4 7 1 0 2 1 2 2.5 0 3.5 3.5 5.5 6.5 5.5 2 0 3-1.5 4.5-3 2-2 5.5-2 7.5 0 1.5 1.5 2.5 3 4.5 3 3 0 6.5-2 6.5-5.5 0-1.5 1-2.5 2-2.5 3 0 5.5-3.5 4-7-1-2-3-3.5-3-6 0-4.5-2.5-7.5-7-7.5-2.5 0-4.5-2-4.5-4 0-2-2-4-5.5-4z" fill="#ffffff" />
                        <path d="M32 16v18M23 26c2 1 2 5 0 7M41 26c-2 1-2 5 0 7M28 20c1.5 1.5 1.5 4 0 5M36 20c-1.5 1.5-1.5 4 0 5M19 33c2.5 1 3.5 4 1 6M45 33c-2.5 1-3.5 4-1 6" stroke="#b8b8b8" />
                        <path d="M30 46 l-4 8 h6 l-2 6 8-10 h-6 z" fill="#b8b8b8" stroke="none" />
                    </svg>
                    <div class="text-[2.6rem] font-medium tracking-tight mt-2 leading-[1.1] flex items-start">
                        <span class="text-[#5bc0de]">ortho</span><span class="text-[#8cc63f]">brain</span><span class="text-[#8cc63f] text-[0.8rem] ml-[1px] mt-2.5">™</span>
                    </div>
                    <div class="text-[12px] italic text-[#6e6b7b] mt-1 tracking-wide">Orthodontics for Your Dental Practice</div>
                </div>
            </header>

            <div class="flex flex-col md:flex-row gap-6 lg:gap-8 items-start relative">
                
                <aside class="w-full md:w-[260px] flex-shrink-0 bg-white rounded-[0.358rem] shadow-[0_4px_24px_0_rgba(34,41,47,0.1)] p-5 sticky top-6 self-start">
                    <nav class="space-y-4" id="scrollspy-nav">
                        <!-- Account -->
                        <div class="flex items-center gap-4 cursor-pointer nav-item active" data-target="step-account">
                            <div class="flex items-center justify-center w-10 h-10 rounded-[0.358rem] nav-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            </div>
                            <div>
                                <div class="text-[0.95rem] nav-text">Account</div>
                                <div class="text-[0.8rem] text-[#b9b9c3]">Account Details</div>
                            </div>
                        </div>
                        
                        <!-- Practice -->
                        <div class="flex items-center gap-4 cursor-pointer nav-item" data-target="step-practice">
                            <div class="flex items-center justify-center w-10 h-10 rounded-[0.358rem] nav-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <div>
                                <div class="text-[0.95rem] nav-text">Practice</div>
                                <div class="text-[0.8rem] text-[#b9b9c3]">Practice Information</div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="flex items-center gap-4 cursor-pointer nav-item" data-target="step-address">
                            <div class="flex items-center justify-center w-10 h-10 rounded-[0.358rem] nav-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <div class="text-[0.95rem] nav-text">Address</div>
                                <div class="text-[0.8rem] text-[#b9b9c3]">Address Information</div>
                            </div>
                        </div>

                        <!-- Additional -->
                        <div class="flex items-center gap-4 cursor-pointer nav-item" data-target="step-additional">
                            <div class="flex items-center justify-center w-10 h-10 rounded-[0.358rem] nav-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <div class="text-[0.95rem] nav-text">Additional</div>
                                <div class="text-[0.8rem] text-[#b9b9c3]">Doctor Information</div>
                            </div>
                        </div>
                    </nav>
                </aside>

                <main class="flex-1 space-y-6 w-full">
                    <form id="registrationForm" action="{{ url('/register') }}" method="POST" novalidate onsubmit="return validateForm(event)">
                        @csrf
                        
                        <!-- Doctor Information Card -->
                        <div id="step-account" class="bg-white rounded-[0.358rem] shadow-[0_4px_24px_0_rgba(34,41,47,0.1)] p-6 scroll-mt-6">
                            <div class="mb-5">
                                <h2 class="text-[1.3rem] font-medium text-vuexy-heading mb-1">Doctor Information</h2>
                                <p class="text-[0.9rem] text-[#6e6b7b]">Enter your account details</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- Email -->
                                <div class="md:col-span-2">
                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Email <span class="text-[#ea5455]">*</span></label>
                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] transition-all overflow-hidden focus-within:border-vuexy-primary focus-within:shadow-[0_0_0_0.2rem_rgba(91,192,222,0.25)]" id="box-email">
                                        <span class="pl-3 pr-2 py-2 text-[#b9b9c3] flex items-center justify-center border-r border-[#d8d6de] bg-white"><svg class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></span>
                                        <input id="in-email" name="email" type="email" required title="Please enter an email address" class="flex-1 px-3 py-[0.5rem] outline-none text-[0.95rem]" placeholder="name@example.com" oninput="clearError('email')" />
                                    </div>
                                    <p id="err-email" class="text-red-500 text-[0.8rem] mt-1 hidden"></p>
                                </div>
                                <!-- First Name -->
                                <div>
                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">First Name<span class="text-[#ea5455]">*</span></label>
                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] transition-all overflow-hidden focus-within:border-vuexy-primary focus-within:shadow-[0_0_0_0.2rem_rgba(91,192,222,0.25)]" id="box-firstName">
                                        <span class="pl-3 pr-2 py-2 text-[#b9b9c3] flex items-center justify-center border-r border-[#d8d6de] bg-white"><svg class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></span>
                                        <input id="in-firstName" name="first_name" type="text" class="flex-1 px-3 py-[0.5rem] bg-white outline-none text-[0.95rem]" placeholder="First name" oninput="clearError('firstName')" />
                                    </div>
                                    <p id="err-firstName" class="text-red-500 text-[0.8rem] mt-1 hidden"></p>
                                </div>
                                <!-- Last Name -->
                                <div>
                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Last Name<span class="text-[#ea5455]">*</span></label>
                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] transition-all overflow-hidden focus-within:border-vuexy-primary focus-within:shadow-[0_0_0_0.2rem_rgba(91,192,222,0.25)]" id="box-lastName">
                                        <span class="pl-3 pr-2 py-2 text-[#b9b9c3] flex items-center justify-center border-r border-[#d8d6de] bg-white"><svg class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></span>
                                        <input id="in-lastName" name="last_name" type="text" class="flex-1 px-3 py-[0.5rem] bg-white outline-none text-[0.95rem]" placeholder="Last name" oninput="clearError('lastName')" />
                                    </div>
                                    <p id="err-lastName" class="text-red-500 text-[0.8rem] mt-1 hidden"></p>
                                </div>
                                <!-- Password -->
                                <div>
                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Password<span class="text-[#ea5455]">*</span></label>
                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] transition-all overflow-hidden focus-within:border-vuexy-primary focus-within:shadow-[0_0_0_0.2rem_rgba(91,192,222,0.25)]" id="box-password">
                                        <span class="pl-3 pr-2 py-2 text-[#b9b9c3] flex items-center justify-center border-r border-[#d8d6de] bg-white"><svg class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg></span>
                                        <input id="in-password" name="password" type="password" class="flex-1 px-3 py-[0.5rem] outline-none text-[0.95rem]" placeholder="********" oninput="clearError('password')" />
                                    </div>
                                    <p id="err-password" class="text-red-500 text-[0.8rem] mt-1 hidden"></p>
                                </div>
                                <!-- Confirm Password -->
                                <div>
                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Confirm Password<span class="text-[#ea5455]">*</span></label>
                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] transition-all overflow-hidden focus-within:border-vuexy-primary focus-within:shadow-[0_0_0_0.2rem_rgba(91,192,222,0.25)]" id="box-confirmPassword">
                                        <span class="pl-3 pr-2 py-2 text-[#b9b9c3] flex items-center justify-center border-r border-[#d8d6de] bg-white"><svg class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg></span>
                                        <input id="in-confirmPassword" name="confirm_password" type="password" class="flex-1 px-3 py-[0.5rem] bg-white outline-none text-[0.95rem]" placeholder="********" oninput="clearError('confirmPassword')" />
                                    </div>
                                    <p id="err-confirmPassword" class="text-red-500 text-[0.8rem] mt-1 hidden"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Practice Information Card -->
                        <div id="step-practice" class="bg-white rounded-[0.358rem] shadow-[0_4px_24px_0_rgba(34,41,47,0.1)] p-6 scroll-mt-6">
                            <div class="mb-5">
                                <h2 class="text-[1.3rem] font-medium text-vuexy-heading mb-1">Practice Information</h2>
                                <p class="text-[0.9rem] text-[#6e6b7b]">Enter your practice details</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- Practice Name -->
                                <div>
                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Practice Name<span class="text-[#ea5455]">*</span></label>
                                    <div class="relative">
                                        <select id="in-practiceName" name="practice_name" required class="w-full h-[2.5rem] px-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-vuexy-primary appearance-none" onchange="clearError('practiceName')">
                                            <option value="" disabled selected>Search practice</option>
                                            <option value="Practice 1">Practice 1</option>
                                            <option value="Practice 2">Practice 2</option>
                                        </select>
                                    </div>
                                    <p id="err-practiceName" class="text-red-500 text-[0.8rem] mt-1 hidden"></p>
                                </div>
                                <!-- Practice Phone Number -->
                                <div>
                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Practice Phone Number<span class="text-[#ea5455]">*</span></label>
                                    <div class="flex rounded-[0.358rem] shadow-sm border border-[#d8d6de] focus-within:border-vuexy-primary focus-within:shadow-[0_0_0_0.2rem_rgba(91,192,222,0.25)] transition-all bg-white" id="box-phone">
                                        <select name="practice_phone_country_code" class="px-3 bg-[#f8f8f8] border-r border-[#d8d6de] text-[0.9rem] text-[#6e6b7b] rounded-l-[0.358rem] outline-none">
                                            <option value="+1_US">+1 (US)</option>
                                            <option value="+1_CA">+1 (CA)</option>
                                            <option value="+61_AU">+61 (AU)</option>
                                        </select>
                                        <input id="in-phone" name="practice_phone_number" type="text" maxlength="10" class="flex-1 px-3 py-[0.5rem] outline-none text-[0.9rem]" placeholder="10 Digit Number" oninput="clearError('phone')"/>
                                    </div>
                                    <p id="err-phone" class="text-red-500 text-[0.8rem] mt-1 hidden"></p>
                                </div>
                                <!-- Practice Website -->
                                <div>
                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Practice Website<span class="text-[#ea5455]">*</span></label>
                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] bg-white transition-all overflow-hidden focus-within:border-vuexy-primary" id="box-website">
                                        <span class="pl-3 pr-2 py-2 text-[#b9b9c3] border-r border-[#d8d6de] bg-white"><svg class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg></span>
                                        <input id="in-website" type="text" name="practice_website" class="flex-1 px-3 py-[0.5rem] bg-transparent outline-none text-[0.95rem]" placeholder="www.example.com" oninput="clearError('website')" />
                                    </div>
                                    <p id="err-website" class="text-red-500 text-[0.8rem] mt-1 hidden"></p>
                                </div>
                                <!-- Preferred Language -->
                                <div>
                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Preferred Language<span class="text-[#ea5455]">*</span></label>
                                    <div class="relative">
                                        <select id="in-language" name="preferred_language" required class="w-full h-[2.5rem] px-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-vuexy-primary appearance-none" onchange="clearError('language')">
                                            <option value="" disabled selected>Select language</option>
                                            <option value="English">English</option>
                                            <option value="Spanish">Spanish</option>
                                            <option value="French">French</option>
                                        </select>
                                    </div>
                                    <p id="err-language" class="text-red-500 text-[0.8rem] mt-1 hidden"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information Card -->
                        <div id="step-address" class="bg-white rounded-[0.358rem] shadow-[0_4px_24px_0_rgba(34,41,47,0.1)] p-6 scroll-mt-6">
                            <div class="mb-5">
                                <h2 class="text-[1.3rem] font-medium text-vuexy-heading mb-1">Address Information</h2>
                            </div>
                            {{-- Hidden inputs populated by the zip auto-fill JS --}}
                            <input type="hidden" name="city_id"    id="hid-city">
                            <input type="hidden" name="state_id"   id="hid-state">
                            <input type="hidden" name="country_id" id="hid-country">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- Street Address -->
                                <div>
                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Street Address<span class="text-[#ea5455]">*</span></label>
                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] bg-white transition-all overflow-hidden focus-within:border-vuexy-primary" id="box-address1">
                                        <input id="in-address1" type="text" name="street_address_1" class="flex-1 px-3 py-[0.5rem] outline-none text-[0.95rem]" placeholder="Street address 1" oninput="clearError('address1')" />
                                    </div>
                                    <p id="err-address1" class="text-red-500 text-[0.8rem] mt-1 hidden"></p>
                                </div>
                                <!-- Street Address 2 -->
                                <div>
                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Street Address 2</label>
                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] bg-white transition-all overflow-hidden focus-within:border-vuexy-primary">
                                        <input id="in-address2" type="text" name="street_address_2" class="flex-1 px-3 py-[0.5rem] outline-none text-[0.95rem]" placeholder="Street address 2" />
                                    </div>
                                </div>
                                <!-- Zip (master-driven) -->
                                <div>
                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Zip<span class="text-[#ea5455]">*</span></label>
                                    <div class="relative">
                                        <select id="in-zip" name="zip_id" required class="w-full h-[2.5rem] px-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-vuexy-primary appearance-none" onchange="onRegZipChange()">
                                            <option value="" disabled selected>Select zip code</option>
                                            @foreach(($zipcodes ?? []) as $z)
                                                <option value="{{ $z->id }}"
                                                        data-city-id="{{ $z->city?->id }}"
                                                        data-city="{{ $z->city?->name }}"
                                                        data-state-id="{{ $z->city?->state?->id }}"
                                                        data-state="{{ $z->city?->state?->name }}"
                                                        data-country-id="{{ $z->city?->state?->country?->id }}"
                                                        data-country="{{ $z->city?->state?->country?->name }}">
                                                    {{ $z->code }} — {{ $z->city?->name }}, {{ $z->city?->state?->state_code }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <p id="err-zip" class="text-red-500 text-[0.8rem] mt-1 hidden"></p>
                                </div>
                                <!-- City (auto-filled, readonly) -->
                                <div>
                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">City<span class="text-[#ea5455]">*</span></label>
                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] bg-[#f8f8f8] overflow-hidden">
                                        <input id="in-city" type="text" class="flex-1 px-3 py-[0.5rem] bg-transparent outline-none text-[0.95rem]" placeholder="Auto-filled from zip" value="" readonly />
                                    </div>
                                </div>
                                <!-- State/Province (auto-filled, readonly) -->
                                <div>
                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">State/Province<span class="text-[#ea5455]">*</span></label>
                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] bg-[#f8f8f8] overflow-hidden">
                                        <input id="in-state" type="text" class="flex-1 px-3 py-[0.5rem] bg-transparent outline-none text-[0.95rem]" placeholder="Auto-filled from zip" value="" readonly />
                                    </div>
                                </div>
                                <!-- Country (auto-filled, readonly) -->
                                <div>
                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Country<span class="text-[#ea5455]">*</span></label>
                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] bg-[#f8f8f8] overflow-hidden">
                                        <input id="in-country" type="text" class="flex-1 px-3 py-[0.5rem] bg-transparent outline-none text-[0.95rem]" placeholder="Auto-filled from zip" value="" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional -->
                        <div id="step-additional" class="bg-white rounded-[0.358rem] shadow-[0_4px_24px_0_rgba(34,41,47,0.1)] p-6 scroll-mt-6">
                            <div class="mb-5 flex justify-between items-start gap-4">
                                <div>
                                    <h2 class="text-[1.3rem] font-medium text-vuexy-heading mb-1">Additional Doctor Information</h2>
                                    <p class="text-[0.9rem] text-[#6e6b7b]">This information will automatically be saved to your account for all future submissions. You may edit this information at any time by visiting the My Profile tab.</p>
                                </div>
                                <button type="button" onclick="toggleAdditionalInfo()" id="btn-toggle-add" class="bg-vuexy-primary text-white rounded-[0.358rem] w-8 h-8 flex items-center justify-center flex-shrink-0 hover:bg-vuexy-hover transition-colors">
                                    <svg id="icon-collapse" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                    <svg id="icon-expand" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </div>

                            <div id="additional-info-body" class="space-y-6 transition-all duration-300">
                                <!-- Orthodontic Services -->
                                <div>
                                    <label class="block text-[0.875rem] font-semibold text-[#5e5873] mb-2">Are you currently providing orthodontic services in your practice?</label>
                                    <div class="space-y-1">
                                        <label class="flex items-center gap-2 cursor-pointer text-[0.875rem] text-[#6e6b7b]">
                                            <input type="radio" name="providing_ortho" value="yes" class="w-4 h-4 text-vuexy-primary focus:ring-vuexy-primary border-[#d8d6de]" /> Yes
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-[0.875rem] text-[#6e6b7b]">
                                            <input type="radio" name="providing_ortho" value="no" class="w-4 h-4 text-vuexy-primary focus:ring-vuexy-primary border-[#d8d6de]" /> No
                                        </label>
                                    </div>
                                </div>

                                <!-- Modalities -->
                                <div>
                                    <label class="block text-[0.875rem] font-semibold text-[#5e5873] mb-2">What modalities are you currently/or planning to provide?</label>
                                    <div class="space-y-1">
                                        <label class="flex items-center gap-2 cursor-pointer text-[0.875rem] text-[#6e6b7b]">
                                            <input type="checkbox" name="modalities[]" value="Clear Aligner Therapy" class="rounded w-4 h-4 text-vuexy-primary focus:ring-vuexy-primary border-[#d8d6de]" /> Clear Aligner Therapy
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-[0.875rem] text-[#6e6b7b]">
                                            <input type="checkbox" name="modalities[]" value="Braces" class="rounded w-4 h-4 text-vuexy-primary focus:ring-vuexy-primary border-[#d8d6de]" /> Braces
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-[0.875rem] text-[#6e6b7b]">
                                            <input type="checkbox" name="modalities[]" value="Early Intervention" class="rounded w-4 h-4 text-vuexy-primary focus:ring-vuexy-primary border-[#d8d6de]" /> Early Intervention
                                        </label>
                                    </div>
                                </div>

                                <!-- Specialties -->
                                <div>
                                    <label class="block text-[0.875rem] font-semibold text-[#5e5873] mb-2">Specialties:</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-1 gap-x-4">
                                        <label class="flex items-center gap-2 cursor-pointer text-[0.875rem] text-[#6e6b7b]">
                                            <input type="checkbox" name="specialties[]" value="General Dentist" class="rounded w-4 h-4 text-vuexy-primary focus:ring-vuexy-primary border-[#d8d6de]" /> General Dentist
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-[0.875rem] text-[#6e6b7b]">
                                            <input type="checkbox" name="specialties[]" value="Oral & Maxillofacial Surgeon" class="rounded w-4 h-4 text-vuexy-primary focus:ring-vuexy-primary border-[#d8d6de]" /> Oral & Maxillofacial Surgeon
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-[0.875rem] text-[#6e6b7b]">
                                            <input type="checkbox" name="specialties[]" value="Orthodontist" class="rounded w-4 h-4 text-vuexy-primary focus:ring-vuexy-primary border-[#d8d6de]" /> Orthodontist
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-[0.875rem] text-[#6e6b7b]">
                                            <input type="checkbox" name="specialties[]" value="Periodontist" class="rounded w-4 h-4 text-vuexy-primary focus:ring-vuexy-primary border-[#d8d6de]" /> Periodontist
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-[0.875rem] text-[#6e6b7b]">
                                            <input type="checkbox" name="specialties[]" value="Pediatric Dentist" class="rounded w-4 h-4 text-vuexy-primary focus:ring-vuexy-primary border-[#d8d6de]" /> Pediatric Dentist
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-[0.875rem] text-[#6e6b7b]">
                                            <input type="checkbox" name="specialties[]" value="Prosthodontist" class="rounded w-4 h-4 text-vuexy-primary focus:ring-vuexy-primary border-[#d8d6de]" /> Prosthodontist
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-[0.875rem] text-[#6e6b7b]">
                                            <input type="checkbox" name="specialties[]" value="Endodontist" class="rounded w-4 h-4 text-vuexy-primary focus:ring-vuexy-primary border-[#d8d6de]" /> Endodontist
                                        </label>
                                    </div>
                                </div>

                                <!-- Preferred doctor contact information -->
                                <div>
                                    <label class="block text-[0.875rem] font-semibold text-[#5e5873] mb-2">Preferred doctor contact information</label>
                                    <div class="space-y-1 mb-4">
                                        <label class="flex items-center gap-2 cursor-pointer text-[0.875rem] text-[#6e6b7b]">
                                            <input type="radio" onchange="toggleContactViews()" name="contact_preference" value="doctor" class="w-4 h-4 text-vuexy-primary focus:ring-vuexy-primary border-[#d8d6de]" /> Doctor Only
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-[0.875rem] text-[#6e6b7b]">
                                            <input type="radio" onchange="toggleContactViews()" name="contact_preference" value="employee" class="w-4 h-4 text-vuexy-primary focus:ring-vuexy-primary border-[#d8d6de]" /> Employee/Office
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-[0.875rem] text-[#6e6b7b]">
                                            <input type="radio" onchange="toggleContactViews()" name="contact_preference" value="both" class="w-4 h-4 text-vuexy-primary focus:ring-vuexy-primary border-[#d8d6de]" /> Doctor and Employee/Office
                                        </label>
                                    </div>

                                    <div id="contact-forms-container" class="space-y-4">
                                        <!-- Doctor Form Box -->
                                        <div id="form-box-doctor" class="hidden bg-[#fbfbfb] border border-[#d8d6de] rounded-[0.358rem] p-5">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                                <div>
                                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Doctor Email<span class="text-[#ea5455]">*</span></label>
                                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] bg-white transition-all overflow-hidden focus-within:border-vuexy-primary">
                                                        <span class="pl-3 pr-2 py-2 text-[#b9b9c3] border-r border-[#d8d6de] bg-white"><svg class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></span>
                                                        <input type="email" name="contact_doctor_email" class="flex-1 px-3 py-[0.5rem] outline-none text-[0.9rem]" placeholder="name@example.com" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Doctor Cell Phone Number</label>
                                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] bg-white transition-all overflow-hidden focus-within:border-vuexy-primary">
                                                        <span class="pl-3 pr-2 py-2 text-[#b9b9c3] border-r border-[#d8d6de] bg-white"><svg class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg></span>
                                                        <input type="text" name="contact_doctor_phone" class="flex-1 px-3 py-[0.5rem] outline-none text-[0.9rem]" placeholder="XXX-XXX-XXXX" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="doctor-other-emails-list" class="space-y-4 mb-4">
                                                <div>
                                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Other Email</label>
                                                    <div class="flex gap-2">
                                                        <div class="relative flex-1 flex items-center border border-[#d8d6de] rounded-[0.358rem] bg-white transition-all overflow-hidden focus-within:border-vuexy-primary">
                                                            <span class="pl-3 pr-2 py-2 text-[#b9b9c3] border-r border-[#d8d6de] bg-white"><svg class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></span>
                                                            <input type="email" name="contact_doctor_other_emails[]" class="flex-1 px-3 py-[0.5rem] outline-none text-[0.9rem]" placeholder="Enter other email" />
                                                        </div>
                                                        <button type="button" onclick="this.parentElement.remove()" class="w-10 h-10 flex items-center justify-center bg-[#ea545520] text-[#ea5455] rounded-[0.358rem] hover:bg-[#ea545530] transition-colors">
                                                            <svg class="h-[1.1rem] w-[1.1rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" onclick="addEmailRow('doctor-other-emails-list')" class="bg-vuexy-primary text-white px-4 py-[0.5rem] rounded-[0.358rem] text-[0.85rem] font-medium hover:bg-vuexy-hover transition-colors shadow-sm">
                                                + Add Other Email
                                            </button>
                                        </div>

                                        <!-- Employee Form Box -->
                                        <div id="form-box-employee" class="hidden bg-[#fbfbfb] border border-[#d8d6de] rounded-[0.358rem] p-5">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                                <div>
                                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Employee Name<span class="text-[#ea5455]">*</span></label>
                                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] bg-white transition-all overflow-hidden focus-within:border-vuexy-primary">
                                                        <span class="pl-3 pr-2 py-2 text-[#b9b9c3] border-r border-[#d8d6de] bg-white"><svg class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></span>
                                                        <input type="text" name="contact_emp_name" class="flex-1 px-3 py-[0.5rem] outline-none text-[0.9rem]" placeholder="Enter name" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Employee Title<span class="text-[#ea5455]">*</span></label>
                                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] bg-white transition-all overflow-hidden focus-within:border-vuexy-primary">
                                                        <span class="pl-3 pr-2 py-2 text-[#b9b9c3] border-r border-[#d8d6de] bg-white"><svg class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg></span>
                                                        <input type="text" name="contact_emp_title" class="flex-1 px-3 py-[0.5rem] outline-none text-[0.9rem]" placeholder="Enter title" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Office/Employee Email<span class="text-[#ea5455]">*</span></label>
                                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] bg-white transition-all overflow-hidden focus-within:border-vuexy-primary">
                                                        <span class="pl-3 pr-2 py-2 text-[#b9b9c3] border-r border-[#d8d6de] bg-white"><svg class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></span>
                                                        <input type="email" name="contact_emp_email" class="flex-1 px-3 py-[0.5rem] outline-none text-[0.9rem]" placeholder="Enter office/employee email" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Office/Employee Cell Phone Number<span class="text-[#ea5455]">*</span></label>
                                                    <div class="relative flex items-center border border-[#d8d6de] rounded-[0.358rem] bg-white transition-all overflow-hidden focus-within:border-vuexy-primary">
                                                        <span class="pl-3 pr-2 py-2 text-[#b9b9c3] border-r border-[#d8d6de] bg-white"><svg class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg></span>
                                                        <input type="text" name="contact_emp_phone" class="flex-1 px-3 py-[0.5rem] outline-none text-[0.9rem]" placeholder="XXX-XXX-XXXX" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="employee-other-emails-list" class="space-y-4 mb-4">
                                                <div>
                                                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Other Email</label>
                                                    <div class="flex gap-2">
                                                        <div class="relative flex-1 flex items-center border border-[#d8d6de] rounded-[0.358rem] bg-white transition-all overflow-hidden focus-within:border-vuexy-primary">
                                                            <span class="pl-3 pr-2 py-2 text-[#b9b9c3] border-r border-[#d8d6de] bg-white"><svg class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></span>
                                                            <input type="email" name="contact_emp_other_emails[]" class="flex-1 px-3 py-[0.5rem] outline-none text-[0.9rem]" placeholder="Enter other email" />
                                                        </div>
                                                        <button type="button" onclick="this.parentElement.remove()" class="w-10 h-10 flex items-center justify-center bg-[#ea545520] text-[#ea5455] rounded-[0.358rem] hover:bg-[#ea545530] transition-colors">
                                                            <svg class="h-[1.1rem] w-[1.1rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" onclick="addEmailRow('employee-other-emails-list')" class="bg-vuexy-primary text-white px-4 py-[0.5rem] rounded-[0.358rem] text-[0.85rem] font-medium hover:bg-vuexy-hover transition-colors shadow-sm">
                                                + Add Other Email
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Doctor Preferences Box -->
                <div class="mb-5 bg-[#fcfcfc] border border-[#ebe9f1] rounded-md overflow-hidden flex flex-col">
                    <div class="bg-white border-b border-[#ebe9f1] p-3 pl-4">
                        <span class="font-bold text-[#5e5873] text-[0.95rem]">Doctor Preferences</span>
                    </div>
                    
                    <div class="overflow-y-auto max-h-[500px] custom-scrollbar bg-white relative">
                        <!-- Preferred Treatment Modality -->
                        <div class="p-4 pt-5 border-b border-[#ebe9f1]">
                            <p class="font-medium text-[#5e5873] mb-3 text-[0.95rem]">Preferred Treatment Modality</p>
                            <div class="space-y-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="treatment_modality" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]">
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Clear Aligner Therapy</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="treatment_modality" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]">
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Braces</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="treatment_modality" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]">
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Orthopedics/Arch Development</span>
                                </label>
                            </div>
                        </div>

                        <!-- Preferred Tooth Numbering System -->
                        <div class="p-4 pt-5 border-b border-[#ebe9f1]">
                            <p class="font-medium text-[#5e5873] mb-3 text-[0.95rem]">Preferred Tooth Numbering System</p>
                            <div class="space-y-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="tooth_numbering" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]" checked>
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Universal (1-32)</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="tooth_numbering" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]">
                                    <span class="text-[#6e6b7b] text-[0.95rem]">FDI (11-48)</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="tooth_numbering" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]">
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Palmer (UR1-UR8)</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="tooth_numbering" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]">
                                    <span class="text-[#6e6b7b] text-[0.95rem]">International (11-48)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Smile Arc -->
                        <div class="p-4 pt-5 border-b border-[#ebe9f1]">
                            <p class="font-medium text-[#5e5873] mb-3 text-[0.95rem]">Smile Arc</p>
                            <div class="space-y-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="smile_arc" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]" checked>
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Defer to orthobrain®</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="smile_arc" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]">
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Lateral incisors .5mm shorter than central incisors</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="smile_arc" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]">
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Lateral incisors same length as central incisors</span>
                                </label>
                            </div>
                        </div>

                        <!-- Treatment of Small Lateral Incisors -->
                        <div class="p-4 pt-5 border-b border-[#ebe9f1]">
                            <p class="font-medium text-[#5e5873] mb-3 text-[0.95rem]">Treatment of Small Lateral Incisors</p>
                            <div class="space-y-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="lateral_incisors" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]" checked>
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Defer to orthobrain®</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="lateral_incisors" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]">
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Interproximal Reduction (IPR) on lower arch to camouflage</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="lateral_incisors" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]">
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Leave spacing mesial and distal to maxillary laterals for future cosmetic correction</span>
                                </label>
                            </div>
                        </div>

                        <!-- Buccal Corridors -->
                        <div class="p-4 pt-5 border-b border-[#ebe9f1]">
                            <p class="font-medium text-[#5e5873] mb-3 text-[0.95rem]">Buccal Corridors</p>
                            <div class="space-y-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="buccal_corridors" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]" checked>
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Defer to orthobrain®</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="buccal_corridors" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]">
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Expand to fill buccal corridors</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="buccal_corridors" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]">
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Do not expand molars</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="buccal_corridors" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]">
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Do not expand premolars or canines</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="buccal_corridors" class="mr-2 w-4 h-4 text-[#5bc0de] focus:ring-[#5bc0de]">
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Maintain initial arch width</span>
                                </label>
                            </div>
                        </div>

                        <!-- Mixed Dentition and Bite Correcting Appliances -->
                        <div class="p-4 pt-5 border-b border-[#ebe9f1]">
                            <p class="font-medium text-[#5e5873] mb-3 text-[0.95rem]">Mixed Dentition and Bite Correcting Appliances</p>
                            <div class="space-y-2">
                                <label class="flex items-start cursor-pointer">
                                    <input type="radio" name="mixed_dentition" class="mr-2 w-4 h-4 mt-1 text-[#5bc0de] focus:ring-[#5bc0de]" checked>
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Defer to orthobrain®</span>
                                </label>
                                <label class="flex items-start cursor-pointer">
                                    <input type="radio" name="mixed_dentition" class="mr-2 w-4 h-4 mt-1 text-[#5bc0de] focus:ring-[#5bc0de]">
                                    <span class="text-[#6e6b7b] text-[0.95rem] leading-relaxed">I prefer not to use any growth and adjunctive appliances (e.g., expanders, bite planes, bit correctors, herbst, etc.) and request a proposal for a best outcome without an appliance knowing and fully understanding that this may not be an ideal Perfect Smile Plan for optimal results.</span>
                                </label>
                            </div>
                        </div>

                        <!-- Orthodontic Extractions -->
                        <div class="p-4 pt-5 border-b border-[#ebe9f1]">
                            <p class="font-medium text-[#5e5873] mb-3 text-[0.95rem]">Orthodontic Extractions</p>
                            <div class="space-y-2">
                                <label class="flex items-start cursor-pointer">
                                    <input type="radio" name="ortho_extractions" class="mr-2 w-4 h-4 mt-1 text-[#5bc0de] focus:ring-[#5bc0de]" checked>
                                    <span class="text-[#6e6b7b] text-[0.95rem]">Defer to orthobrain®</span>
                                </label>
                                <label class="flex items-start cursor-pointer">
                                    <input type="radio" name="ortho_extractions" class="mr-2 w-4 h-4 mt-1 text-[#5bc0de] focus:ring-[#5bc0de]">
                                    <span class="text-[#6e6b7b] text-[0.95rem] leading-relaxed">I prefer not to extract teeth and request a proposal for a best outcome without extractions fully knowing and fully understanding that this may not be an ideal treatment plan for optimal results.</span>
                                </label>
                            </div>
                        </div>

                        <!-- Preferences (Toggles) -->
                        <div class="p-4 pt-5 pb-6 relative">
                            <p class="font-medium text-[#5e5873] mb-4 text-[0.95rem]">Preferences</p>
                            <div class="space-y-5">
                                
                                <!-- IPR Protocol -->
                                <div>
                                    <label class="flex items-center cursor-pointer">
                                        <div class="relative">
                                            <input type="checkbox" class="sr-only" onchange="document.getElementById('ipr-options').classList.toggle('hidden')">
                                            <div class="w-10 h-[22px] bg-[#ebe9f1] rounded-full shadow-inner custom-toggle-bg transition-colors"></div>
                                            <div class="dot absolute w-4 h-4 bg-white rounded-full shadow right-1 top-[3px] transition-transform"></div>
                                        </div>
                                        <div class="ml-3 text-[0.95rem] text-[#6e6b7b]">IPR Protocol</div>
                                    </label>
                                    <div id="ipr-options" class="hidden mt-3 p-4 bg-[#f8f8f8] rounded-md space-y-2 border border-[#ebe9f1]">
                                        <label class="flex items-center cursor-pointer"><input type="radio" name="ipr_opt" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de]" checked><span class="text-[#6e6b7b] text-[0.9rem]">Defer to orthobrain®</span></label>
                                        <label class="flex items-center cursor-pointer"><input type="radio" name="ipr_opt" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de]"><span class="text-[#6e6b7b] text-[0.9rem]">No IPR</span></label>
                                        <label class="flex items-center cursor-pointer"><input type="radio" name="ipr_opt" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de]"><span class="text-[#6e6b7b] text-[0.9rem]">Other</span></label>
                                    </div>
                                </div>

                                <!-- Attachments -->
                                <div>
                                    <label class="flex items-center cursor-pointer">
                                        <div class="relative">
                                            <input type="checkbox" class="sr-only" onchange="document.getElementById('attachment-options').classList.toggle('hidden')">
                                            <div class="w-10 h-[22px] bg-[#ebe9f1] rounded-full shadow-inner custom-toggle-bg transition-colors"></div>
                                            <div class="dot absolute w-4 h-4 bg-white rounded-full shadow right-1 top-[3px] transition-transform"></div>
                                        </div>
                                        <div class="ml-3 text-[0.95rem] text-[#6e6b7b]">Attachments</div>
                                    </label>
                                    <div id="attachment-options" class="hidden mt-3 p-4 bg-[#f8f8f8] rounded-md space-y-2 border border-[#ebe9f1]">
                                        <label class="flex items-center cursor-pointer"><input type="radio" name="attachment_opt" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de]" checked><span class="text-[#6e6b7b] text-[0.9rem]">At Aligner Step 1</span></label>
                                        <label class="flex items-center cursor-pointer"><input type="radio" name="attachment_opt" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de]"><span class="text-[#6e6b7b] text-[0.9rem]">At Aligner Step</span></label>
                                    </div>
                                </div>

                                <!-- Elastics/Bonded Buttons -->
                                <div>
                                    <label class="flex items-center cursor-pointer">
                                        <div class="relative">
                                            <input type="checkbox" class="sr-only" onchange="document.getElementById('elastics-options').classList.toggle('hidden')">
                                            <div class="w-10 h-[22px] bg-[#ebe9f1] rounded-full shadow-inner custom-toggle-bg transition-colors"></div>
                                            <div class="dot absolute w-4 h-4 bg-white rounded-full shadow right-1 top-[3px] transition-transform"></div>
                                        </div>
                                        <div class="ml-3 text-[0.95rem] text-[#6e6b7b]">Elastics/Bonded Buttons</div>
                                    </label>
                                    <div id="elastics-options" class="hidden mt-3 p-4 bg-[#f8f8f8] rounded-md space-y-2 border border-[#ebe9f1]">
                                        <label class="flex items-center cursor-pointer"><input type="radio" name="elastics_opt" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de]" checked><span class="text-[#6e6b7b] text-[0.9rem]">Yes</span></label>
                                        <label class="flex items-center cursor-pointer"><input type="radio" name="elastics_opt" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de]"><span class="text-[#6e6b7b] text-[0.9rem]">No</span></label>
                                    </div>
                                </div>

                                <!-- Extractions if suggested -->
                                <div>
                                    <label class="flex items-center cursor-pointer">
                                        <div class="relative">
                                            <input type="checkbox" class="sr-only" onchange="document.getElementById('extractions-options').classList.toggle('hidden')">
                                            <div class="w-10 h-[22px] bg-[#ebe9f1] rounded-full shadow-inner custom-toggle-bg transition-colors"></div>
                                            <div class="dot absolute w-4 h-4 bg-white rounded-full shadow right-1 top-[3px] transition-transform"></div>
                                        </div>
                                        <div class="ml-3 text-[0.95rem] text-[#6e6b7b]">Extractions if suggested</div>
                                    </label>
                                    <div id="extractions-options" class="hidden mt-3 p-4 bg-[#f8f8f8] rounded-md space-y-2 border border-[#ebe9f1]">
                                        <label class="flex items-center cursor-pointer"><input type="radio" name="extractions_opt" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de]" checked><span class="text-[#6e6b7b] text-[0.9rem]">Yes</span></label>
                                        <label class="flex items-center cursor-pointer"><input type="radio" name="extractions_opt" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de]"><span class="text-[#6e6b7b] text-[0.9rem]">No</span></label>
                                    </div>
                                </div>
                                
                            </div>
                            
                            <!-- Back to Top pseudo-button matching screenshot -->
                            <div class="sticky bottom-0 bg-[#5bc0de] text-white w-8 h-8 rounded-[0.358rem] flex justify-center items-center cursor-pointer opacity-90 shadow float-right mt-[-30px] z-10 hover:bg-[#46b8da]" onclick="this.parentElement.parentElement.scrollTo({top: 0, behavior: 'smooth'});">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                            </div>

                            <!-- Terms and SMS Checkboxes -->
                            <div class="mb-6 space-y-2 mt-8 px-2">
                                <label class="flex items-start text-[#6e6b7b] text-[0.9rem] cursor-pointer">
                                    <input type="checkbox" name="terms_agreed" onchange="clearError('terms')" class="mt-[0.2rem] mr-3 w-[1.1rem] h-[1.1rem] text-[#5bc0de] focus:ring-[#5bc0de] border-[#d8d6de] rounded-[0.2rem] transition-all cursor-pointer" />
                                    <span>By creating an account at orthobrain you accept the <a href="#" class="text-[#5bc0de] hover:underline cursor-pointer">Terms and Conditions</a>.</span>
                                </label>
                                <p id="err-terms" class="text-red-500 text-[0.8rem] mt-1 hidden"></p>
                                <label class="flex items-start text-[#6e6b7b] text-[0.9rem] cursor-pointer">
                                    <input type="checkbox" name="sms_agreed" class="mt-[0.2rem] mr-3 w-[1.1rem] h-[1.1rem] text-[#5bc0de] focus:ring-[#5bc0de] border-[#d8d6de] rounded-[0.2rem] transition-all cursor-pointer" />
                                    <span class="flex items-center">I agree to receive SMS messages for authentication purposes. 
                                        <svg class="w-[1.1rem] h-[1.1rem] ml-1.5 text-[#5e5873] opacity-70 cursor-pointer" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                                    </span>
                                </label>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
                        <!-- Action Buttons -->
                        <div class="flex justify-end items-center gap-4 mt-6 border-t border-[#ebe9f1] pt-6">
                            <a href="{{ url('/login') }}" class="px-5 py-[0.6rem] bg-[#5bc0de] hover:bg-[#46b8da] text-white rounded-[0.358rem] font-medium shadow-sm transition-colors text-[0.95rem]">Back to Login</a>
                            <button type="button" onclick="validateForm()" class="px-5 py-[0.6rem] bg-[#8cc63f] hover:bg-[#7cb038] text-white rounded-[0.358rem] font-medium shadow-sm transition-colors text-[0.95rem] tracking-wide">Submit</button>
                        </div>
                    </form>
                </main>
            </div>
        </div>

        <script>
            // Additional Doctor Info UI Logic
            function toggleAdditionalInfo() {
                const body = document.getElementById('additional-info-body');
                const iconExpand = document.getElementById('icon-expand');
                const iconCollapse = document.getElementById('icon-collapse');
                
                if (body.classList.contains('hidden')) {
                    body.classList.remove('hidden');
                    iconExpand.classList.add('hidden');
                    iconCollapse.classList.remove('hidden');
                } else {
                    body.classList.add('hidden');
                    iconExpand.classList.remove('hidden');
                    iconCollapse.classList.add('hidden');
                }
            }

            function toggleContactViews() {
                const selected = document.querySelector('input[name="contact_preference"]:checked');
                const docForm = document.getElementById('form-box-doctor');
                const empForm = document.getElementById('form-box-employee');
                
                docForm.classList.add('hidden');
                empForm.classList.add('hidden');
                
                if (selected) {
                    if (selected.value === 'doctor') {
                        docForm.classList.remove('hidden');
                    } else if (selected.value === 'employee') {
                        empForm.classList.remove('hidden');
                    } else if (selected.value === 'both') {
                        docForm.classList.remove('hidden');
                        empForm.classList.remove('hidden');
                    }
                }
            }

            function toggleIprNote() {
                const iprSelect = document.getElementById('in-ipr-protocol');
                const noteContainer = document.getElementById('ipr-note-container');
                if (iprSelect && iprSelect.value === 'OTHER') {
                    noteContainer.classList.remove('hidden');
                } else {
                    noteContainer.classList.add('hidden');
                }
            }

            function addEmailRow(containerId) {
                const container = document.getElementById(containerId);
                const isDoctor = containerId.includes('doctor');
                const inputName = isDoctor ? 'contact_doctor_other_emails[]' : 'contact_emp_other_emails[]';
                
                const row = document.createElement('div');
                row.className = 'flex gap-2';
                row.innerHTML = `
                    <div class="relative flex-1 flex items-center border border-[#d8d6de] rounded-[0.358rem] bg-white transition-all overflow-hidden focus-within:border-vuexy-primary">
                        <span class="pl-3 pr-2 py-2 text-[#b9b9c3] border-r border-[#d8d6de] bg-white">
                            <svg class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </span>
                        <input type="email" name="${inputName}" class="flex-1 px-3 py-[0.5rem] outline-none text-[0.9rem]" placeholder="Enter other email" />
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="w-10 h-10 flex items-center justify-center bg-[#ea545520] text-[#ea5455] rounded-[0.358rem] hover:bg-[#ea545530] transition-colors">
                        <svg class="h-[1.1rem] w-[1.1rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                `;
                container.appendChild(row);
            }

            // ── Zip auto-fill: reads data-* on the selected option and populates
            //    city / state / country readonly fields + hidden FK inputs.
            function onRegZipChange() {
                clearError('zip');
                const sel = document.getElementById('in-zip');
                const opt = sel?.options[sel.selectedIndex];
                const city    = document.getElementById('in-city');
                const state   = document.getElementById('in-state');
                const country = document.getElementById('in-country');
                const hCity   = document.getElementById('hid-city');
                const hState  = document.getElementById('hid-state');
                const hCty    = document.getElementById('hid-country');
                if (!opt || !opt.value) {
                    [city, state, country, hCity, hState, hCty].forEach(el => { if (el) el.value = ''; });
                    return;
                }
                if (city)    city.value    = opt.dataset.city    || '';
                if (state)   state.value   = opt.dataset.state   || '';
                if (country) country.value = opt.dataset.country || '';
                if (hCity)   hCity.value   = opt.dataset.cityId    || '';
                if (hState)  hState.value  = opt.dataset.stateId   || '';
                if (hCty)    hCty.value    = opt.dataset.countryId || '';
            }

            // ScrollSpy Navigation Logic
            const navItems = document.querySelectorAll('.nav-item');
            const sections = document.querySelectorAll('div[id^="step-"]');

            let isNavigating = false;
            let navTimer = null;

            function setActive(id) {
                navItems.forEach(item => {
                    if (item.getAttribute('data-target') === id) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });
            }

            function updateActiveNav() {
                if (isNavigating) return;
                const triggerPoint = window.innerHeight * 0.2;
                let activeId = sections[0] ? sections[0].id : null;
                sections.forEach(sec => {
                    if (sec.getBoundingClientRect().top <= triggerPoint) {
                        activeId = sec.id;
                    }
                });
                setActive(activeId);
            }

            window.addEventListener('scroll', updateActiveNav, { passive: true });
            updateActiveNav();

            // Smooth Scroll on nav item click
            navItems.forEach(item => {
                item.addEventListener('click', () => {
                    const targetId = item.getAttribute('data-target');
                    const target = document.getElementById(targetId);
                    if (!target) return;

                    isNavigating = true;
                    clearTimeout(navTimer);
                    setActive(targetId);

                    navTimer = setTimeout(() => {
                        isNavigating = false;
                        updateActiveNav();
                    }, 900);

                    window.scrollTo({
                        top: window.scrollY + target.getBoundingClientRect().top - 24,
                        behavior: 'smooth'
                    });
                });
            });

            // ── Form Validation Logic ──────────────────────────────
            const emailRe    = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const nameRe     = /^[A-Za-z\s\-]+$/;
            const websiteRe  = /^(https?:\/\/)?([\da-z\.\-]+)\.([a-z\.]{2,6})([\/\w \.\-]*)*\/?$/i;
            const pwSpecial  = /[!@#$%^&*()\-_+={}\[\]:;<>,.?~\\/]/;

            // Per-field validators: id → (value) → '' when OK, else error message.
            // The `all` arg is used for cross-field rules (e.g. confirmPassword).
            const VALIDATORS = {
                email: v => !v ? 'Email is required'
                    : !emailRe.test(v) ? 'Please enter a valid email address' : '',
                firstName: v => !v ? 'First name is required'
                    : v.length < 2 ? 'First name must be at least 2 characters'
                    : !nameRe.test(v) ? 'Only letters, spaces, and hyphens are allowed' : '',
                lastName: v => !v ? 'Last name is required'
                    : v.length < 2 ? 'Last name must be at least 2 characters'
                    : !nameRe.test(v) ? 'Only letters, spaces, and hyphens are allowed' : '',
                password: v => !v ? 'Password is required'
                    : (v.length < 8 || !/[A-Z]/.test(v) || !/[a-z]/.test(v) || !/\d/.test(v) || !pwSpecial.test(v))
                        ? 'Min 8 characters with uppercase, lowercase, number & special character' : '',
                confirmPassword: (v, all) => {
                    if (!v) return 'Please confirm your password';
                    if (v !== all.password) return 'Passwords do not match';
                    return '';
                },
                practiceName: v => !v ? 'Please select a practice' : '',
                phone: v => !v ? 'Phone number is required'
                    : !/^\d{10}$/.test(v) ? 'Please enter a valid 10-digit phone number' : '',
                website: v => !v ? 'Website is required'
                    : !websiteRe.test(v) ? 'Please enter a valid website (e.g. www.example.com)' : '',
                language: v => !v ? 'Please select a preferred language' : '',
                address1: v => !v ? 'Street address is required'
                    : v.length < 5 ? 'Please enter a complete street address (min 5 characters)' : '',
                zip: v => !v ? 'Please select a zip code' : '',
            };

            // Which section each field belongs to (for scroll-on-submit-error).
            const FIELD_SECTION = {
                email: 'step-account', firstName: 'step-account', lastName: 'step-account',
                password: 'step-account', confirmPassword: 'step-account',
                practiceName: 'step-practice', phone: 'step-practice',
                website: 'step-practice', language: 'step-practice',
                address1: 'step-address', zip: 'step-address',
            };

            const touched = new Set();

            function _fieldValue(fieldId) {
                const el = document.getElementById('in-' + fieldId);
                if (!el) return '';
                return (el.value || '').trim();
            }
            function _allValues() {
                const out = {};
                Object.keys(VALIDATORS).forEach(id => { out[id] = _fieldValue(id); });
                // password is compared raw (no trim) for confirmPassword rule; keep raw separately
                out.password = document.getElementById('in-password')?.value ?? '';
                out.confirmPassword = document.getElementById('in-confirmPassword')?.value ?? '';
                return out;
            }

            function showError(fieldId, errorMsg) {
                const errElement = document.getElementById('err-' + fieldId);
                const boxElement = document.getElementById('box-' + fieldId);
                const inElement = document.getElementById('in-' + fieldId);
                if (errElement) { errElement.textContent = errorMsg; errElement.classList.remove('hidden'); }
                if (boxElement) boxElement.classList.add('border-red-500');
                else if (inElement) inElement.classList.add('border-red-500');
            }

            function _clearUI(fieldId) {
                const errElement = document.getElementById('err-' + fieldId);
                const boxElement = document.getElementById('box-' + fieldId);
                const inElement = document.getElementById('in-' + fieldId);
                if (errElement) errElement.classList.add('hidden');
                if (boxElement) boxElement.classList.remove('border-red-500');
                else if (inElement) inElement.classList.remove('border-red-500');
            }

            // Validate one field. Used by blur/input listeners and by submit.
            function validateField(fieldId) {
                const v = VALIDATORS[fieldId];
                if (!v) return true;
                const all = _allValues();
                const msg = v(all[fieldId], all);
                if (msg) { showError(fieldId, msg); return false; }
                _clearUI(fieldId);
                return true;
            }

            // Called by `oninput="clearError(...)"` in the markup.
            // If the field has been touched (blurred once), re-run validation live
            // so the error updates as the user fixes / re-breaks the field.
            function clearError(fieldId) {
                if (touched.has(fieldId)) {
                    validateField(fieldId);
                    // cross-field: retyping password should re-check confirmPassword
                    if (fieldId === 'password' && touched.has('confirmPassword')) {
                        validateField('confirmPassword');
                    }
                } else {
                    _clearUI(fieldId);
                }
            }

            // Wire blur + change listeners for live validation.
            document.addEventListener('DOMContentLoaded', () => {
                Object.keys(VALIDATORS).forEach(fieldId => {
                    const el = document.getElementById('in-' + fieldId);
                    if (!el) return;
                    el.addEventListener('blur', () => {
                        touched.add(fieldId);
                        validateField(fieldId);
                        if (fieldId === 'password' && touched.has('confirmPassword')) {
                            validateField('confirmPassword');
                        }
                    });
                    if (el.tagName === 'SELECT') {
                        el.addEventListener('change', () => {
                            touched.add(fieldId);
                            validateField(fieldId);
                        });
                    }
                });

                // Terms checkbox live feedback
                const terms = document.querySelector('input[name="terms_agreed"]');
                if (terms) {
                    terms.addEventListener('change', () => {
                        const err = document.getElementById('err-terms');
                        if (terms.checked && err) err.classList.add('hidden');
                    });
                }
            });

            // Submit handler — validate every field, scroll to first error section.
            function validateForm() {
                // Mark everything touched so all errors surface for someone who
                // clicked Submit without interacting with the form.
                Object.keys(VALIDATORS).forEach(id => touched.add(id));

                let isValid = true;
                let firstErrorSection = null;
                const mark = (id) => { if (!firstErrorSection) firstErrorSection = id; };

                Object.keys(VALIDATORS).forEach(fieldId => {
                    if (!validateField(fieldId)) {
                        isValid = false;
                        mark(FIELD_SECTION[fieldId]);
                    }
                });

                // Zip picked but city_id wasn't populated (auto-fill failed) — block submit.
                const zipVal = document.getElementById('in-zip')?.value;
                const hiddenCity = document.getElementById('hid-city')?.value;
                if (zipVal && !hiddenCity) {
                    showError('zip', 'Zip lookup failed. Please reselect the zip code.');
                    mark('step-address');
                    isValid = false;
                }

                const termsCheckbox = document.querySelector('input[name="terms_agreed"]');
                if (termsCheckbox && !termsCheckbox.checked) {
                    showError('terms', 'You must accept the Terms and Conditions to continue');
                    mark('step-additional');
                    isValid = false;
                }

                if (!isValid && firstErrorSection) {
                    const target = document.getElementById(firstErrorSection);
                    if (target) {
                        isNavigating = true;
                        clearTimeout(navTimer);
                        setActive(firstErrorSection);
                        navTimer = setTimeout(() => { isNavigating = false; updateActiveNav(); }, 900);
                        window.scrollTo({
                            top: window.scrollY + target.getBoundingClientRect().top - 24,
                            behavior: 'smooth'
                        });
                    }
                    return false;
                }

                if (isValid) {
                    document.getElementById('registrationForm').submit();
                }
                return isValid;
            }
        </script>
    </body>
</html>
