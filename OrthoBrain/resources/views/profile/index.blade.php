@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="w-full">
    <!-- Header -->
    <div class="bg-white rounded-t-md shadow-sm border border-[#d8d6de] border-b-0 p-5">
        <h2 class="text-[1.4rem] text-[#5e5873] font-medium">My Profile</h2>
    </div>

    <!-- Inner Layout -->
    <div class="flex flex-col md:flex-row bg-white rounded-b-md shadow-sm border border-[#d8d6de]">
        
        <!-- Vertical Tabs Nav -->
        <div class="w-full md:w-64 border-r border-[#ebe9f1] p-4 flex flex-col space-y-1">
            <a href="?tab=account" class="px-4 py-2 {{ $tab == 'account' ? 'bg-[#5bc0de] text-white shadow-sm' : 'text-[#6e6b7b] hover:text-[#5bc0de]' }} rounded-[0.358rem] text-[0.95rem] flex items-center transition-colors">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Account
            </a>
            <a href="?tab=practice" class="px-4 py-2 {{ $tab == 'practice' ? 'bg-[#5bc0de] text-white shadow-sm' : 'text-[#6e6b7b] hover:text-[#5bc0de]' }} rounded-[0.358rem] text-[0.95rem] flex items-center transition-colors">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0z"></path></svg>
                Practice
            </a>
            <a href="?tab=shipping" class="px-4 py-2 {{ $tab == 'shipping' ? 'bg-[#5bc0de] text-white shadow-sm' : 'text-[#6e6b7b] hover:text-[#5bc0de]' }} rounded-[0.358rem] text-[0.95rem] flex items-center transition-colors">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                Shipping Address
            </a>
            <a href="?tab=billing" class="px-4 py-2 {{ $tab == 'billing' ? 'bg-[#5bc0de] text-white shadow-sm' : 'text-[#6e6b7b] hover:text-[#5bc0de]' }} rounded-[0.358rem] text-[0.95rem] flex items-center transition-colors">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                Billing Address
            </a>
            <a href="?tab=additional" class="px-4 py-2 {{ $tab == 'additional' ? 'bg-[#5bc0de] text-white shadow-sm' : 'text-[#6e6b7b] hover:text-[#5bc0de]' }} rounded-[0.358rem] text-[0.95rem] flex items-center transition-colors">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Additional Information
            </a>
        </div>

        <!-- Dynamic Form Context -->
        <div class="flex-1 p-6 overflow-x-hidden">
            
            @if(session('success'))
                <div class="bg-[#e2f8eb] text-[#28c76f] px-4 py-2 rounded mb-4 text-[0.9rem]">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-[#ffebed] text-[#ea5455] px-4 py-2 rounded mb-4 text-[0.9rem]">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- // TAB: ACCOUNT // -->
            @if($tab == 'account')
            <h3 class="text-[1.2rem] font-medium text-[#5e5873] mb-6">Account</h3>

            <form id="accountForm" method="POST" action="/dev/profile/index?tab=account" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- First Name -->
                    <div>
                        <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">First Name<span class="text-[#ea5455]">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none border-r border-[#d8d6de] pr-2 bg-[#f8f8f8] rounded-l-[0.358rem]">
                                <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <input id="in-acc-first-name" type="text" name="first_name" value="{{ old('first_name', \App\Models\Doctor::where('user_id', auth()->id())->first()?->first_name ?? '') }}" class="w-full h-[2.5rem] pl-12 pr-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem]">
                        </div>
                        <p id="err-acc-first-name" class="hidden text-red-500 text-[0.78rem] mt-0.5"></p>
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Last Name<span class="text-[#ea5455]">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none border-r border-[#d8d6de] pr-2 bg-[#f8f8f8] rounded-l-[0.358rem]">
                                <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <input id="in-acc-last-name" type="text" name="last_name" value="{{ old('last_name', \App\Models\Doctor::where('user_id', auth()->id())->first()?->last_name ?? '') }}" class="w-full h-[2.5rem] pl-12 pr-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem]">
                        </div>
                        <p id="err-acc-last-name" class="hidden text-red-500 text-[0.78rem] mt-0.5"></p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Email<span class="text-[#ea5455]">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none border-r border-[#d8d6de] pr-2 bg-[#f8f8f8] rounded-l-[0.358rem]">
                                <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <input id="in-acc-email" type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" class="w-full h-[2.5rem] pl-12 pr-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem]">
                        </div>
                        <p id="err-acc-email" class="hidden text-red-500 text-[0.78rem] mt-0.5"></p>
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Phone Number</label>
                        <div class="flex">
                            <div class="relative w-1/3 mr-2">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                </div>
                                <select name="practice_phone_country_code" class="w-full h-[2.5rem] pl-10 pr-1 bg-[#f8f8f8] border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] appearance-none text-[0.9rem]">
                                    <option value="+1_US" selected>+1 (US)</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                                    <svg class="w-3 h-3 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                            <input id="in-acc-phone" type="text" name="practice_phone_number" value="{{ old('practice_phone_number', \App\Models\Doctor::where('user_id', auth()->id())->first()?->practice_phone_number ?? '') }}" placeholder="XXX-XXX-XXXX" class="flex-1 h-[2.5rem] px-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem]">
                        </div>
                        <p id="err-acc-phone" class="hidden text-red-500 text-[0.78rem] mt-0.5"></p>
                    </div>
                </div>

                <!-- Profile Image Dropzone -->
                <div class="mb-6">
                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Profile Image</label>
                    <div class="w-full h-48 border-[2px] border-dashed border-[#d8d6de] rounded flex flex-col items-center justify-center text-[#5e5873] hover:bg-[#f8f8f8] cursor-pointer transition-colors relative bg-[#fcfcfc]">
                        <svg class="w-10 h-10 mb-2 text-[#a5a8b6]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <span class="text-[1.3rem] font-medium">Choose a File or Drag & Drop Files</span>
                        <input type="file" name="profile_image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*">
                    </div>
                </div>

                <!-- Notifications -->
                <div class="flex gap-x-24 mb-6 pt-2">
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" class="sr-only" checked>
                                <div class="w-10 h-[18px] bg-[#5bc0de] rounded-full shadow-inner block"></div>
                                <div class="dot absolute w-[14px] h-[14px] bg-white rounded-full shadow right-1 top-[2px] transition"></div>
                            </div>
                            <div class="ml-3 text-[0.95rem] text-[#5e5873] font-medium">Notification</div>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" class="sr-only" checked>
                                <div class="w-10 h-[18px] bg-[#5bc0de] rounded-full shadow-inner block"></div>
                                <div class="dot absolute w-[14px] h-[14px] bg-white rounded-full shadow right-1 top-[2px] transition"></div>
                            </div>
                            <div class="ml-3 text-[0.95rem] text-[#5e5873] font-medium">Notification</div>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3 mt-4">
                    <button type="button" onclick="validateAccountForm()" class="bg-[#8cc63f] hover:bg-[#7ab133] text-white text-[0.95rem] px-5 py-[0.4rem] rounded focus:outline-none transition-colors border border-[#8cc63f]">Save</button>
                    <a href="/dev/cases/list" class="bg-[#ea5455] hover:bg-[#d84042] text-white text-[0.95rem] px-5 py-[0.4rem] rounded focus:outline-none transition-colors border border-[#ea5455]">Cancel</a>
                </div>
            </form>
            @endif


            <!-- // TAB: PRACTICE // -->
            @if($tab == 'practice')
            <h3 class="text-[1.2rem] font-medium text-[#5e5873] mb-6 border-b border-[#ebe9f1] pb-3">Practice</h3>

            <form id="practiceForm" method="POST" action="/dev/profile/index?tab=practice" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Practice Name -->
                    <div>
                        <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Practice Name<span class="text-[#ea5455]">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none border-r border-[#d8d6de] pr-2 bg-[#f8f8f8] rounded-l-[0.358rem]">
                                <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                            </div>
                            <input id="in-prac-name" type="text" name="practice_name" value="{{ old('practice_name', \App\Models\Doctor::where('user_id', auth()->id())->first()?->practice_name ?? '') }}" class="w-full h-[2.5rem] pl-12 pr-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem] text-[#6e6b7b]">
                        </div>
                        <p id="err-prac-name" class="hidden text-red-500 text-[0.78rem] mt-0.5"></p>
                    </div>

                    <!-- Practice Phone Number -->
                    <div>
                        <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Practice Phone Number<span class="text-[#ea5455]">*</span></label>
                        <div class="flex">
                            <div class="relative w-1/3 mr-2">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                </div>
                                <select name="practice_phone_country_code" class="w-full h-[2.5rem] pl-10 pr-1 bg-[#f8f8f8] border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] appearance-none text-[0.9rem]">
                                    <option value="+1_US" selected>+1 (US)</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                                    <svg class="w-3 h-3 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                            <input id="in-prac-phone" type="text" name="practice_phone_number" value="{{ old('practice_phone_number', \App\Models\Doctor::where('user_id', auth()->id())->first()?->practice_phone_number ?? '') }}" placeholder="XXX-XXX-XXXX" class="flex-1 h-[2.5rem] px-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem] text-[#6e6b7b]">
                        </div>
                        <p id="err-prac-phone" class="hidden text-red-500 text-[0.78rem] mt-0.5"></p>
                    </div>

                    <!-- Website -->
                    <div>
                        <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Website<span class="text-[#ea5455]">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none border-r border-[#d8d6de] pr-2 bg-[#f8f8f8] rounded-l-[0.358rem]">
                                <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                            </div>
                            <input id="in-prac-website" type="text" name="website" value="{{ old('website', \App\Models\Doctor::where('user_id', auth()->id())->first()?->practice_website ?? '') }}" placeholder="https://yoursite.com" class="w-full h-[2.5rem] pl-12 pr-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem] text-[#6e6b7b]">
                        </div>
                        <p id="err-prac-website" class="hidden text-red-500 text-[0.78rem] mt-0.5"></p>
                    </div>

                    <!-- Preferred Language -->
                    <div>
                        <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Preferred Language</label>
                        <div class="relative">
                            <select name="language" class="w-full h-[2.5rem] pl-3 pr-8 bg-[#f8f8f8] border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] appearance-none text-[0.9rem] text-[#6e6b7b]">
                                <option value="English" selected>English</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Practice Logo -->
                <div class="mb-6">
                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Practice Logo</label>
                    <div class="w-full h-48 border-[2px] border-dashed border-[#d8d6de] rounded flex flex-col items-center justify-center text-[#5e5873] hover:bg-[#f8f8f8] cursor-pointer transition-colors relative bg-[#fcfcfc] mt-2 group">
                        
                        <!-- Tooltip replica attached to the icon block -->
                        <div class="absolute flex flex-col items-center justify-center mt-6">
                            <div class="bg-[#283046] text-white text-[12px] py-1 px-2 rounded -mt-[50px] mr-24 relative hidden group-hover:block whitespace-nowrap">
                                No file selected.
                                <svg class="absolute text-[#283046] h-2 w-full left-0 top-full" x="0px" y="0px" viewBox="0 0 255 255" xml:space="preserve"><polygon class="fill-current" points="0,0 127.5,127.5 255,0"/></svg>
                            </div>
                            <svg class="w-10 h-10 mb-2 text-[#a5a8b6]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            <span class="text-[1.3rem] font-medium">Choose a File or Drag & Drop Files</span>
                        </div>
                        
                        <input type="file" name="practice_logo" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*">
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3 mt-4">
                    <button type="button" onclick="validatePracticeForm()" class="bg-[#8cc63f] hover:bg-[#7ab133] text-white text-[0.95rem] px-5 py-[0.4rem] rounded focus:outline-none transition-colors border border-[#8cc63f]">Save</button>
                    <a href="/dev/profile/index" class="bg-[#ea5455] hover:bg-[#d84042] text-white text-[0.95rem] px-5 py-[0.4rem] rounded focus:outline-none transition-colors border border-[#ea5455]">Cancel</a>
                </div>
            </form>
            @endif


            <!-- // TAB: SHIPPING ADDRESS // -->
            @if($tab == 'shipping')
            <div class="flex justify-between items-center mb-6 pt-1">
                <h3 class="text-[1.4rem] font-medium text-[#5e5873]">Shipping Addresses</h3>
                <button class="bg-[#5bc0de] hover:bg-[#46b8da] text-white text-[0.95rem] px-4 py-[0.35rem] rounded-[0.358rem] font-medium flex items-center shadow-sm">
                    <svg class="w-4 h-4 mr-1 pb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Add Shipping Address
                </button>
            </div>

            <!-- Filters -->
            <div class="flex justify-between items-center border-t border-b border-[#ebe9f1] py-3 text-[0.9rem] text-[#6e6b7b] mb-1">
                <div class="flex items-center">
                    <span>Show</span>
                    <select class="mx-2 px-2 py-1 bg-white border border-[#d8d6de] rounded outline-none w-16 appearance-none">
                        <option>10</option>
                    </select>
                    <span>entries</span>
                </div>
                <div class="flex items-center">
                    <span class="mr-2 text-[#5e5873]">Search:</span>
                    <input type="text" class="border border-[#d8d6de] outline-none px-3 py-1 rounded w-48 focus:border-[#5bc0de]">
                </div>
            </div>

            <!-- Table -->
            <div class="w-full overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#f3f2f7] text-[#5e5873] text-[0.85rem] font-bold tracking-widest border-b border-[#ebe9f1] uppercase">
                            <th class="py-[0.8rem] px-4">Action</th>
                            <th class="py-[0.8rem] px-4 cursor-pointer">Street Address <span class="text-[#b9b9c3] inline-block ml-1 opacity-60">⇅</span></th>
                            <th class="py-[0.8rem] px-4 cursor-pointer">City <span class="text-[#b9b9c3] inline-block ml-1 opacity-60">⇅</span></th>
                            <th class="py-[0.8rem] px-4 cursor-pointer">State/Province <span class="text-[#b9b9c3] inline-block ml-1 opacity-60">⇅</span></th>
                            <th class="py-[0.8rem] px-4 cursor-pointer">Zip Code <span class="text-[#b9b9c3] inline-block ml-1 opacity-60">⇅</span></th>
                            <th class="py-[0.8rem] px-4">Is Default</th>
                        </tr>
                    </thead>
                    <tbody class="text-[0.95rem] text-[#6e6b7b]">
                        <tr class="border-b border-[#ebe9f1] hover:bg-[#f9f8f9]">
                            <td class="py-3 px-4 flex items-center space-x-2">
                                <a href="#" class="text-[#28c76f] hover:opacity-80"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></a>
                                <a href="#" class="text-[#5bc0de] hover:opacity-80"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></a>
                            </td>
                            <td class="py-3 px-4">Test12</td>
                            <td class="py-3 px-4 w-20 break-words leading-tight">Delaw<br>are</td>
                            <td class="py-3 px-4">Ohio</td>
                            <td class="py-3 px-4">43015</td>
                            <td class="py-3 px-4">
                                <span class="bg-[#c2e49c] text-white px-5 py-[2px] rounded text-[0.8rem] font-medium inline-block">Yes</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="px-2 py-4 flex justify-between items-center text-[0.85rem] text-[#b9b9c3] border-t border-[#ebe9f1] mt-1">
                <span>Showing 1 to 1 of 1 entries</span>
                <div class="flex space-x-1">
                    <button class="bg-[#f3f2f7] text-[#b9b9c3] px-3 py-1.5 rounded-[0.358rem] cursor-not-allowed">Previous</button>
                    <button class="bg-[#5bc0de] text-white px-3 py-1.5 rounded-[0.358rem]">1</button>
                    <button class="bg-[#f3f2f7] text-[#b9b9c3] px-3 py-1.5 rounded-[0.358rem] cursor-not-allowed">Next</button>
                </div>
            </div>
            @endif


            <!-- // TAB: BILLING ADDRESS // -->
            @if($tab == 'billing')
            <div class="flex justify-between items-center mb-6 pt-1">
                <h3 class="text-[1.4rem] font-medium text-[#5e5873]">Billing Addresses</h3>
                <button class="bg-[#5bc0de] hover:bg-[#46b8da] text-white text-[0.95rem] px-4 py-[0.35rem] rounded-[0.358rem] font-medium flex items-center shadow-sm">
                    <svg class="w-4 h-4 mr-1 pb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Add Billing Address
                </button>
            </div>

            <!-- Filters -->
            <div class="flex justify-between items-center border-t border-b border-[#ebe9f1] py-3 text-[0.9rem] text-[#6e6b7b] mb-1">
                <div class="flex items-center">
                    <span>Show</span>
                    <select class="mx-2 px-2 py-1 bg-white border border-[#d8d6de] rounded outline-none w-16 appearance-none">
                        <option>10</option>
                    </select>
                    <span>entries</span>
                </div>
                <div class="flex items-center">
                    <span class="mr-2 text-[#5e5873]">Search:</span>
                    <input type="text" class="border border-[#d8d6de] outline-none px-3 py-1 rounded w-48 focus:border-[#5bc0de]">
                </div>
            </div>

            <!-- Table -->
            <div class="w-full overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#f3f2f7] text-[#5e5873] text-[0.8rem] font-bold tracking-widest border-b border-[#ebe9f1] uppercase">
                            <th class="py-[0.8rem] px-4">Action</th>
                            <th class="py-[0.8rem] px-4 cursor-pointer">Street Address <span class="text-[#b9b9c3] inline-block ml-1 opacity-60">⇅</span></th>
                            <th class="py-[0.8rem] px-4 cursor-pointer">City <span class="text-[#b9b9c3] inline-block ml-1 opacity-60">⇅</span></th>
                            <th class="py-[0.8rem] px-4 cursor-pointer">State/Province <span class="text-[#b9b9c3] inline-block ml-1 opacity-60">⇅</span></th>
                            <th class="py-[0.8rem] px-4 cursor-pointer">Zip Code <span class="text-[#b9b9c3] inline-block ml-1 opacity-60">⇅</span></th>
                            <th class="py-[0.8rem] px-4 cursor-pointer">Billing Email <span class="text-[#b9b9c3] inline-block ml-1 opacity-60">⇅</span></th>
                        </tr>
                    </thead>
                    <tbody class="text-[0.95rem] text-[#6e6b7b]">
                        <tr class="border-b border-[#ebe9f1]">
                            <td colspan="6" class="py-4 px-4 text-center text-[#6e6b7b]">No data available in table</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="px-2 py-4 flex justify-between items-center text-[0.85rem] text-[#b9b9c3] border-t border-[#ebe9f1] mt-1">
                <span>Showing 0 to 0 of 0 entries</span>
                <div class="flex space-x-2">
                    <button class="bg-[#f8f8f8] text-[#b9b9c3] px-3 py-1.5 rounded-[0.358rem] cursor-not-allowed">Previous</button>
                    <button class="bg-[#f8f8f8] text-[#b9b9c3] px-3 py-1.5 rounded-[0.358rem] cursor-not-allowed">Next</button>
                </div>
            </div>
            @endif


            <!-- // TAB: ADDITIONAL INFORMATION // -->
            @if($tab == 'additional')
            <h3 class="text-[1.2rem] font-medium text-[#5e5873] mb-3">Additional Information</h3>

            <div class="text-[0.875rem] text-[#5e5873] mb-6">
                <!-- Ortho Services Radio -->
                <div class="mb-5">
                    <p class="font-semibold mb-2">Are you currently providing orthodontic services in your practice?</p>
                    <label class="flex items-center mb-1 cursor-pointer">
                        <input type="radio" name="ortho_services" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de]" checked>
                        <span class="text-[#6e6b7b]">Yes</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="ortho_services" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de]">
                        <span class="text-[#6e6b7b]">No</span>
                    </label>
                </div>

                <!-- Modalities Checkboxes -->
                <div class="mb-5">
                    <p class="font-semibold mb-2">What modalities are you currently/or planning to provide?</p>
                    <label class="flex items-center mb-1 mt-1 cursor-pointer">
                        <input type="checkbox" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de] rounded-[0.25rem] border-[#d8d6de]">
                        <span class="text-[#6e6b7b]">Clear Aligner Therapy</span>
                    </label>
                    <label class="flex items-center mb-1 cursor-pointer">
                        <input type="checkbox" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de] rounded-[0.25rem] border-[#d8d6de]">
                        <span class="text-[#6e6b7b]">Braces</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de] rounded-[0.25rem] border-[#d8d6de]">
                        <span class="text-[#6e6b7b]">Early Intervention</span>
                    </label>
                </div>

                <!-- Specialties Grid -->
                <div class="mb-5">
                    <p class="font-semibold mb-2">Specialties:</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-1">
                        <!-- Left Column -->
                        <div>
                            <label class="flex items-center mb-1 cursor-pointer">
                                <input type="checkbox" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de] rounded-[0.25rem] border-[#d8d6de]">
                                <span class="text-[#6e6b7b]">General Dentist</span>
                            </label>
                            <label class="flex items-center mb-1 cursor-pointer">
                                <input type="checkbox" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de] rounded-[0.25rem] border-[#d8d6de]">
                                <span class="text-[#6e6b7b]">Orthodontist</span>
                            </label>
                            <label class="flex items-center mb-1 cursor-pointer">
                                <input type="checkbox" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de] rounded-[0.25rem] border-[#d8d6de]">
                                <span class="text-[#6e6b7b]">Pediatric Dentist</span>
                            </label>
                            <label class="flex items-center mb-1 cursor-pointer">
                                <input type="checkbox" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de] rounded-[0.25rem] border-[#d8d6de]">
                                <span class="text-[#6e6b7b]">Endodontist</span>
                            </label>
                        </div>
                        <!-- Right Column -->
                        <div>
                            <label class="flex items-center mb-1 cursor-pointer">
                                <input type="checkbox" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de] rounded-[0.25rem] border-[#d8d6de]">
                                <span class="text-[#6e6b7b]">Oral & Maxillofacial Surgeon</span>
                            </label>
                            <label class="flex items-center mb-1 cursor-pointer">
                                <input type="checkbox" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de] rounded-[0.25rem] border-[#d8d6de]">
                                <span class="text-[#6e6b7b]">Periodontist</span>
                            </label>
                            <label class="flex items-center mb-1 cursor-pointer">
                                <input type="checkbox" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de] rounded-[0.25rem] border-[#d8d6de]">
                                <span class="text-[#6e6b7b]">Prosthodontist</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Preferred Doctor Contact Radio -->
                <div class="mb-5">
                    <p class="font-semibold mb-2">Preferred doctor contact information</p>
                    <label class="flex items-center mb-1 cursor-pointer">
                        <input type="radio" name="contact_info" value="doctor_only" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de] scale-110" checked onchange="toggleContactForms()">
                        <span class="text-[#6e6b7b]">Doctor Only</span>
                    </label>
                    <label class="flex items-center mb-1 cursor-pointer">
                        <input type="radio" name="contact_info" value="employee_office" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de] scale-110" onchange="toggleContactForms()">
                        <span class="text-[#6e6b7b]">Employee/Office</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="contact_info" value="doctor_and_employee" class="mr-2 text-[#5bc0de] focus:ring-[#5bc0de] scale-110" onchange="toggleContactForms()">
                        <span class="text-[#6e6b7b]">Doctor and Employee/Office</span>
                    </label>
                </div>

                <!-- Dynamic Contact Forms Container -->
                <div class="flex flex-col gap-4 mt-2 mb-8">
                    
                    <!-- Doctor Contact Block -->
                    <div id="form-doctor" class="bg-[#fcfcfc] border border-[#ebe9f1] rounded-md p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <!-- Doctor Email -->
                            <div>
                                <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Doctor Email<span class="text-[#ea5455]">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none border-r border-[#d8d6de] pr-2 bg-[#f8f8f8] rounded-l-[0.358rem]">
                                        <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <input id="in-doc-email" type="email" value="{{ \App\Models\Doctor::where('user_id', auth()->id())->first()?->doctor_contact_email ?? '' }}" class="w-full h-[2.5rem] pl-12 pr-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem] text-[#6e6b7b]">
                                </div>
                                <p id="err-doc-email" class="hidden text-red-500 text-[0.78rem] mt-0.5"></p>
                            </div>
                            <!-- Doctor Cell Phone Number -->
                            <div>
                                <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Doctor Cell Phone Number</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none border-r border-[#d8d6de] pr-2 bg-[#f8f8f8] rounded-l-[0.358rem]">
                                        <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    </div>
                                    <input id="in-doc-phone" type="text" placeholder="XXX-XXX-XXXX" value="{{ \App\Models\Doctor::where('user_id', auth()->id())->first()?->doctor_cell_phone ?? '' }}" class="w-full h-[2.5rem] pl-12 pr-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem]">
                                </div>
                                <p id="err-doc-phone" class="hidden text-red-500 text-[0.78rem] mt-0.5"></p>
                            </div>
                        </div>

                        <!-- Other Emails (Doctor) -->
                        <div class="mb-3">
                            <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-2">Other Email</label>
                            <div id="doc-other-emails" class="flex flex-col gap-2"></div>
                        </div>
                        <button type="button" onclick="addOtherEmail('doc')" class="bg-[#5bc0de] hover:bg-[#46b8da] text-white px-4 py-[0.4rem] rounded-[0.358rem] text-[0.9rem] flex items-center shadow-sm">
                            <span class="mr-1 text-[1.2rem] pb-[1px]">+</span> Add Other Email
                        </button>
                    </div>

                    <!-- Employee Contact Block -->
                    <div id="form-employee" class="bg-[#fcfcfc] border border-[#ebe9f1] rounded-md p-5 hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <!-- Employee Name -->
                            <div>
                                <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Employee Name<span class="text-[#ea5455]">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none border-r border-[#d8d6de] pr-2 bg-[#f8f8f8] rounded-l-[0.358rem]">
                                        <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <input id="in-emp-name" type="text" placeholder="Enter name" class="w-full h-[2.5rem] pl-12 pr-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem] text-[#6e6b7b] placeholder-[#b9b9c3]">
                                </div>
                                <p id="err-emp-name" class="hidden text-red-500 text-[0.78rem] mt-0.5"></p>
                            </div>
                            <!-- Employee Title -->
                            <div>
                                <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Employee Title<span class="text-[#ea5455]">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none border-r border-[#d8d6de] pr-2 bg-[#f8f8f8] rounded-l-[0.358rem]">
                                        <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <input id="in-emp-title" type="text" placeholder="Enter title" class="w-full h-[2.5rem] pl-12 pr-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem] placeholder-[#b9b9c3]">
                                </div>
                                <p id="err-emp-title" class="hidden text-red-500 text-[0.78rem] mt-0.5"></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <!-- Office/Employee Email -->
                            <div>
                                <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Office/Employee Email<span class="text-[#ea5455]">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none border-r border-[#d8d6de] pr-2 bg-[#f8f8f8] rounded-l-[0.358rem]">
                                        <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <input id="in-emp-email" type="email" placeholder="Enter office/employee email" class="w-full h-[2.5rem] pl-12 pr-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem] placeholder-[#b9b9c3]">
                                </div>
                                <p id="err-emp-email" class="hidden text-red-500 text-[0.78rem] mt-0.5"></p>
                            </div>
                            <!-- Office/Employee Cell Phone Number -->
                            <div>
                                <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Office/Employee Cell Phone Number<span class="text-[#ea5455]">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none border-r border-[#d8d6de] pr-2 bg-[#f8f8f8] rounded-l-[0.358rem]">
                                        <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    </div>
                                    <input id="in-emp-phone" type="text" placeholder="XXX-XXX-XXXX" class="w-full h-[2.5rem] pl-12 pr-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem] placeholder-[#b9b9c3]">
                                </div>
                                <p id="err-emp-phone" class="hidden text-red-500 text-[0.78rem] mt-0.5"></p>
                            </div>
                        </div>

                        <!-- Other Emails (Employee) -->
                        <div class="mb-3">
                            <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-2">Other Email</label>
                            <div id="emp-other-emails" class="flex flex-col gap-2"></div>
                        </div>
                        <button type="button" onclick="addOtherEmail('emp')" class="bg-[#5bc0de] hover:bg-[#46b8da] text-white px-4 py-[0.4rem] rounded-[0.358rem] text-[0.9rem] flex items-center shadow-sm">
                            <span class="mr-1 text-[1.2rem] pb-[1px]">+</span> Add Other Email
                        </button>
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
                        </div>
                    </div>
                </div>
                
                <!-- Save and Cancel Buttons -->
                <div class="mt-8 flex space-x-4 mb-4">
                    <button type="button" onclick="validateContactForm()" class="bg-[#5bc0de] hover:bg-[#46b8da] text-white px-5 py-[0.6rem] rounded-[0.358rem] font-medium shadow-sm transition-colors cursor-pointer tracking-wide text-[0.9rem]">
                        Save changes
                    </button>
                    <button type="button" class="border border-[#d8d6de] text-[#6e6b7b] bg-transparent hover:bg-[#f8f8f8] px-5 py-[0.6rem] rounded-[0.358rem] font-medium transition-colors cursor-pointer tracking-wide text-[0.9rem]">
                        Cancel
                    </button>
                </div>

            </div>
            
            <script>
                function toggleContactForms() {
                    const selected = document.querySelector('input[name="contact_info"]:checked').value;
                    const doctorForm = document.getElementById('form-doctor');
                    const employeeForm = document.getElementById('form-employee');
                    if (selected === 'doctor_only') {
                        doctorForm.classList.remove('hidden');
                        employeeForm.classList.add('hidden');
                    } else if (selected === 'employee_office') {
                        doctorForm.classList.add('hidden');
                        employeeForm.classList.remove('hidden');
                    } else if (selected === 'doctor_and_employee') {
                        doctorForm.classList.remove('hidden');
                        employeeForm.classList.remove('hidden');
                    }
                }
                document.addEventListener('DOMContentLoaded', toggleContactForms);

                /* ── Add / Remove Other Email rows ── */
                const _emailRowHTML = `
                    <div class="flex items-center space-x-3 other-email-row">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none border-r border-[#d8d6de] pr-2 bg-[#f8f8f8] rounded-l-[0.358rem]">
                                <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <input type="email" placeholder="Enter other email" class="w-full h-[2.5rem] pl-12 pr-3 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem] placeholder-[#b9b9c3]">
                        </div>
                        <button type="button" onclick="this.closest('.other-email-row').remove()" class="bg-[#ffebed] hover:bg-[#ffcfd4] text-[#ea5455] w-12 h-[2.5rem] rounded-[0.358rem] flex justify-center items-center transition-colors shadow-sm cursor-pointer flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>`;

                function addOtherEmail(prefix) {
                    const container = document.getElementById(prefix + '-other-emails');
                    const div = document.createElement('div');
                    div.innerHTML = _emailRowHTML.trim();
                    container.appendChild(div.firstChild);
                }

                /* ── Contact form validation ── */
                function _cErr(id, msg) {
                    const e = document.getElementById('err-' + id);
                    const i = document.getElementById('in-' + id);
                    if (e) { e.textContent = msg; e.classList.remove('hidden'); }
                    if (i) i.classList.add('border-red-500');
                }
                function _cClear(id) {
                    const e = document.getElementById('err-' + id);
                    const i = document.getElementById('in-' + id);
                    if (e) { e.textContent = ''; e.classList.add('hidden'); }
                    if (i) i.classList.remove('border-red-500');
                }

                function validateContactForm() {
                    let ok = true;
                    ['doc-email','doc-phone','emp-name','emp-title','emp-email','emp-phone'].forEach(_cClear);

                    const mode = document.querySelector('input[name="contact_info"]:checked')?.value || 'doctor_only';
                    const useDoc = mode === 'doctor_only' || mode === 'doctor_and_employee';
                    const useEmp = mode === 'employee_office' || mode === 'doctor_and_employee';

                    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    if (useDoc) {
                        const de = (document.getElementById('in-doc-email')?.value || '').trim();
                        if (!de) { _cErr('doc-email', 'Doctor email is required.'); ok = false; }
                        else if (!emailRe.test(de)) { _cErr('doc-email', 'Enter a valid email address.'); ok = false; }

                        const dp = (document.getElementById('in-doc-phone')?.value || '').trim();
                        if (dp && dp.replace(/\D/g,'').length !== 10) { _cErr('doc-phone', 'Phone must be 10 digits.'); ok = false; }

                        document.querySelectorAll('#doc-other-emails input[type="email"]').forEach(inp => {
                            const v = inp.value.trim();
                            if (v && !emailRe.test(v)) { inp.classList.add('border-red-500'); ok = false; }
                            else inp.classList.remove('border-red-500');
                        });
                    }

                    if (useEmp) {
                        const en = (document.getElementById('in-emp-name')?.value || '').trim();
                        if (!en) { _cErr('emp-name', 'Employee name is required.'); ok = false; }
                        else if (en.length < 2) { _cErr('emp-name', 'Minimum 2 characters.'); ok = false; }

                        const et = (document.getElementById('in-emp-title')?.value || '').trim();
                        if (!et) { _cErr('emp-title', 'Employee title is required.'); ok = false; }
                        else if (et.length < 2) { _cErr('emp-title', 'Minimum 2 characters.'); ok = false; }

                        const ee = (document.getElementById('in-emp-email')?.value || '').trim();
                        if (!ee) { _cErr('emp-email', 'Office/employee email is required.'); ok = false; }
                        else if (!emailRe.test(ee)) { _cErr('emp-email', 'Enter a valid email address.'); ok = false; }

                        const ep = (document.getElementById('in-emp-phone')?.value || '').trim();
                        if (!ep) { _cErr('emp-phone', 'Phone number is required.'); ok = false; }
                        else if (ep.replace(/\D/g,'').length !== 10) { _cErr('emp-phone', 'Phone must be 10 digits.'); ok = false; }

                        document.querySelectorAll('#emp-other-emails input[type="email"]').forEach(inp => {
                            const v = inp.value.trim();
                            if (v && !emailRe.test(v)) { inp.classList.add('border-red-500'); ok = false; }
                            else inp.classList.remove('border-red-500');
                        });
                    }

                    if (useDoc && useEmp) {
                        const de = (document.getElementById('in-doc-email')?.value || '').trim().toLowerCase();
                        const ee = (document.getElementById('in-emp-email')?.value || '').trim().toLowerCase();
                        if (de && ee && de === ee) { _cErr('emp-email', 'Office/employee email must differ from doctor email.'); ok = false; }
                    }

                    if (ok) alert('Contact information is valid! (Backend save coming soon.)');
                }
            </script>
            @endif

        </div>
    </div>
</div>

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

<script>
/* ── Account Tab Validation ── */
function _accErr(id, msg) {
    const e = document.getElementById('err-acc-' + id);
    const i = document.getElementById('in-acc-' + id);
    if (e) { e.textContent = msg; e.classList.remove('hidden'); }
    if (i) i.classList.add('border-red-500');
}
function _accClear(id) {
    const e = document.getElementById('err-acc-' + id);
    const i = document.getElementById('in-acc-' + id);
    if (e) { e.textContent = ''; e.classList.add('hidden'); }
    if (i) i.classList.remove('border-red-500');
}
function validateAccountForm() {
    let ok = true;
    ['first-name', 'last-name', 'email', 'phone'].forEach(_accClear);

    const fn = (document.getElementById('in-acc-first-name')?.value || '').trim();
    if (!fn) { _accErr('first-name', 'First name is required.'); ok = false; }
    else if (fn.length < 2) { _accErr('first-name', 'Minimum 2 characters.'); ok = false; }
    else if (!/^[A-Za-z\s\-]+$/.test(fn)) { _accErr('first-name', 'Letters, spaces and hyphens only.'); ok = false; }

    const ln = (document.getElementById('in-acc-last-name')?.value || '').trim();
    if (!ln) { _accErr('last-name', 'Last name is required.'); ok = false; }
    else if (ln.length < 2) { _accErr('last-name', 'Minimum 2 characters.'); ok = false; }
    else if (!/^[A-Za-z\s\-]+$/.test(ln)) { _accErr('last-name', 'Letters, spaces and hyphens only.'); ok = false; }

    const em = (document.getElementById('in-acc-email')?.value || '').trim();
    if (!em) { _accErr('email', 'Email is required.'); ok = false; }
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)) { _accErr('email', 'Enter a valid email address.'); ok = false; }

    const phRaw = (document.getElementById('in-acc-phone')?.value || '').trim();
    if (phRaw && phRaw.replace(/\D/g, '').length !== 10) {
        _accErr('phone', 'Phone must be 10 digits (e.g. 5551234567).'); ok = false;
    }

    if (ok) document.getElementById('accountForm').submit();
}

/* ── Practice Tab Validation ── */
function _pracErr(id, msg) {
    const e = document.getElementById('err-prac-' + id);
    const i = document.getElementById('in-prac-' + id);
    if (e) { e.textContent = msg; e.classList.remove('hidden'); }
    if (i) i.classList.add('border-red-500');
}
function _pracClear(id) {
    const e = document.getElementById('err-prac-' + id);
    const i = document.getElementById('in-prac-' + id);
    if (e) { e.textContent = ''; e.classList.add('hidden'); }
    if (i) i.classList.remove('border-red-500');
}
function validatePracticeForm() {
    let ok = true;
    ['name', 'phone', 'website'].forEach(_pracClear);

    const pn = (document.getElementById('in-prac-name')?.value || '').trim();
    if (!pn) { _pracErr('name', 'Practice name is required.'); ok = false; }
    else if (pn.length < 3) { _pracErr('name', 'Minimum 3 characters.'); ok = false; }

    const ph = (document.getElementById('in-prac-phone')?.value || '').trim().replace(/\D/g, '');
    if (!ph) { _pracErr('phone', 'Practice phone is required.'); ok = false; }
    else if (ph.length !== 10) { _pracErr('phone', 'Phone must be 10 digits (e.g. 5551234567).'); ok = false; }

    const ws = (document.getElementById('in-prac-website')?.value || '').trim();
    if (!ws) { _pracErr('website', 'Website is required.'); ok = false; }
    else if (!/^(https?:\/\/)?([\w\-]+\.)+[\w]{2,}(\/\S*)?$/.test(ws)) {
        _pracErr('website', 'Enter a valid URL (e.g. https://yoursite.com).'); ok = false;
    }

    if (ok) document.getElementById('practiceForm').submit();
}
</script>
@endsection
