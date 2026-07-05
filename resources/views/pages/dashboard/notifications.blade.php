@php $dashLayout = !empty(session('siac_user')['is_admin']) ? 'layouts.admin' : 'layouts.dashboard'; @endphp
@extends($dashLayout)

@php
$pageTitle = $lang === 'fr' ? 'Notifications' : 'Notifications';
$typeIcons = [
    'new_message' => 'message-circle',
    'verification_approved' => 'badge-check',
    'verification_rejected' => 'shield-x',
    'business_suspended' => 'ban',
    'business_rejected' => 'x-circle',
];
@endphp

@section('content')
<div class="max-w-2xl">

    <div class="flex justify-end mb-3">
        <a href="{{ route('notifications.settings') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#8A857A] hover:text-forest-600 transition-colors">
            <i data-lucide="settings-2" class="w-3.5 h-3.5"></i>
            {{ $lang === 'fr' ? 'Préférences' : 'Preferences' }}
        </a>
    </div>

    <div class="bg-white border border-[#EFEBE2] rounded-xl overflow-hidden">
        @forelse($notifications as $n)
        <a href="{{ $n->link ?? '#' }}" class="flex items-start gap-3 px-4 py-3.5 border-b border-[#FBF9F4] last:border-0 hover:bg-[#FBF9F4] transition-colors">
            <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="{{ $typeIcons[$n->type] ?? 'bell' }}" class="w-4 h-4 text-forest-600"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-[#1B1B18]">{{ $n->title }}</p>
                @if($n->body)<p class="text-xs text-[#8A857A] mt-0.5">{{ $n->body }}</p>@endif
                <p class="text-[10px] text-[#A8A296] mt-1">{{ $n->created_at->diffForHumans() }}</p>
            </div>
            @if(! $n->read_at)<span class="w-2 h-2 rounded-full bg-forest-500 shrink-0 mt-1.5"></span>@endif
        </a>
        @empty
        <div class="text-center py-14 px-4">
            <i data-lucide="bell-off" class="w-10 h-10 text-[#EFEBE2] mx-auto mb-3"></i>
            <p class="text-sm text-[#A8A296]">{{ $lang === 'fr' ? 'Aucune notification pour l\'instant.' : 'No notifications yet.' }}</p>
        </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="mt-4">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
