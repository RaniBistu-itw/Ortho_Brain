@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="w-full">
    <!-- Header -->
    <div class="bg-white rounded-t-md shadow-sm border border-[#d8d6de] border-b-0 p-5">
        <h2 class="text-[1.4rem] text-[#5e5873] font-medium">Change Password</h2>
    </div>

    <!-- Inner Context -->
    <div class="bg-white rounded-b-md shadow-sm border border-[#d8d6de] p-6">
        <form method="POST" action="/dev/profile/settings">
            @csrf
            
            <div class="mb-6">
                <!-- Old Password -->
                <div class="mb-5">
                    <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Old Password<span class="text-[#ea5455]">*</span></label>
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input type="password" name="old_password" required placeholder="..........." class="w-full h-[2.5rem] pl-10 pr-10 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem] tracking-widest text-[#6e6b7b]">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer text-[#b9b9c3] hover:text-[#5e5873]" onclick="togglePassword(this)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- New Password -->
                    <div>
                        <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">New Password<span class="text-[#ea5455]">*</span></label>
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <input type="password" name="new_password" required placeholder="..........." class="w-full h-[2.5rem] pl-10 pr-10 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem] tracking-widest text-[#6e6b7b]">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer text-[#b9b9c3] hover:text-[#5e5873]" onclick="togglePassword(this)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label class="block text-[0.85rem] font-medium text-[#5e5873] mb-1">Confirm New Password<span class="text-[#ea5455]">*</span></label>
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-[#b9b9c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <input type="password" name="new_password_confirmation" required placeholder="..........." class="w-full h-[2.5rem] pl-10 pr-10 bg-white border border-[#d8d6de] rounded-[0.358rem] outline-none focus:border-[#5bc0de] text-[0.9rem] tracking-widest text-[#6e6b7b]">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer text-[#b9b9c3] hover:text-[#5e5873]" onclick="togglePassword(this)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Validation Errors -->
            @if(session('success'))
                <div class="bg-[#e2f8eb] text-[#28c76f] px-4 py-2 rounded mb-4 text-[0.9rem] max-w-2xl">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-[#ffebed] text-[#ea5455] px-4 py-2 rounded mb-4 text-[0.9rem] max-w-2xl">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Actions -->
            <div class="flex space-x-3 mt-8">
                <button type="submit" class="bg-[#8cc63f] hover:bg-[#7ab133] text-white text-[0.95rem] font-medium px-5 py-2 rounded focus:outline-none transition-colors">Update Password</button>
                <a href="/dev/cases/list" class="bg-[#ea5455] hover:bg-[#d84042] text-white text-[0.95rem] font-medium px-5 py-2 rounded focus:outline-none transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePassword(element) {
        const input = element.previousElementSibling;
        if (input.type === 'password') {
            input.type = 'text';
            element.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>';
        } else {
            input.type = 'password';
            element.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>';
        }
    }
</script>
@endsection
