@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'adminsupport';
    $ref = '#TK-' . \Carbon\Carbon::parse($ticket->created_at)->format('Y') . '-' . str_pad((string)$ticket->id, 4, '0', STR_PAD_LEFT);
    $statusMeta = ['open'=>[$isFr?'Ouvert':'Open','#3565DE'],'in_progress'=>[$isFr?'En cours':'In progress','#C97A16'],'resolved'=>[$isFr?'Résolu':'Resolved','#157A43'],'closed'=>[$isFr?'Fermé':'Closed','#8A857A']];
    [$stLabel,$stColor] = $statusMeta[$ticket->status] ?? [$ticket->status,'#8A857A'];
    $prioMeta = ['high'=>[$isFr?'Haute':'High','#DC2626'],'medium'=>[$isFr?'Moyenne':'Medium','#C97A16'],'low'=>[$isFr?'Basse':'Low','#157A43']];
    [$prLabel,$prColor] = $prioMeta[$ticket->priority] ?? [$ticket->priority,'#8A857A'];
    $subj = $isFr ? $ticket->subject_fr : ($ticket->subject_en ?? $ticket->subject_fr);
    $dtf = fn($v)=> $v ? \Carbon\Carbon::parse($v)->format('d M Y, H:i') : '—';
    $metaRow = [
        ['tag', $isFr?'Catégorie':'Category', 'KYC & Vérification'],
        ['trending-up', $isFr?'Priorité':'Priority', $prLabel],
        ['loader', 'Statut', $stLabel],
        ['user-check', $isFr?'Assigné à':'Assigned to', $isFr?'Équipe Support':'Support Team'],
        ['calendar', $isFr?'Échéance':'Due', \Carbon\Carbon::parse($ticket->created_at)->addDays(7)->format('d M Y')],
        ['mail', 'Canal', 'Email'],
    ];
    $pageTitle = $isFr?'DÉTAILS DU TICKET':'TICKET DETAILS';
    $pageBreadcrumb = [['Accueil', route('dashboard.admin')],['Tickets', route('admin.support')],[$ref, null]];
    $pageSearchPlaceholder = $isFr?'Rechercher un ticket, un utilisateur...':'Search a ticket, a user...';
@endphp

@section('content')
            <div class="grid grid-cols-1 2xl:grid-cols-[1fr_320px] gap-5 items-start">
                <div class="space-y-5">
                    {{-- Ticket header --}}
                    <section class="ui-card">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <span class="w-[54px] h-[54px] rounded-xl bg-[#E8F2EC] dark:bg-[#1A1E16] flex items-center justify-center shrink-0"><i data-lucide="file-text" class="w-6 h-6 text-[#157A43] dark:text-[#339B56]"></i></span>
                                <div>
                                    <span class="inline-block rounded-md px-2.5 py-0.5 text-[13px] md:text-[11px] font-semibold" style="background-color: {{ $stColor }}1a;color: {{ $stColor }}">{{ $stLabel }}</span>
                                    <p class="mt-1 text-[15px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $ref }}</p>
                                    <p class="text-[16px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $subj }}</p>
                                    <p class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-[13px] md:text-[11.5px] text-[#6F6B60] dark:text-[#868778]"><span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5"></i>{{ $dtf($ticket->created_at) }}</span><span class="flex items-center gap-1.5"><i data-lucide="user" class="w-3.5 h-3.5"></i>{{ $isFr?'Par':'By' }} {{ $ticket->user_name ?? 'Client' }}</span><span class="flex items-center gap-1.5"><i data-lucide="mail" class="w-3.5 h-3.5"></i>via Email</span></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <a href="{{ route('admin.support', ['lang'=>$lang]) }}" class="ui-btn ui-btn-secondary"><i data-lucide="settings" class="w-4 h-4"></i>Actions</a>
                                <a href="#reply" class="ui-btn ui-btn-primary"><i data-lucide="reply" class="w-4 h-4"></i>{{ $isFr?'Répondre':'Reply' }}</a>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 border-t border-[#EFEBE2] dark:border-[#262B21] pt-4">
                            @foreach($metaRow as [$mI,$mL,$mV])<div class="flex items-start gap-2"><i data-lucide="{{ $mI }}" class="w-4 h-4 mt-0.5 text-[#C9942E] dark:text-[#EDB33A] shrink-0"></i><div><p class="text-[12px] md:text-[10px] text-[#8A857A] dark:text-[#868778]">{{ $mL }}</p><p class="text-[14px] md:text-[12px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $mV }}</p></div></div>@endforeach
                        </div>
                    </section>

                    {{-- Conversation --}}
                    <section class="ui-card">
                        <div class="flex items-center gap-6 border-b border-[#EFEBE2] dark:border-[#262B21]"><span class="pb-3 text-[13px] font-semibold text-[#14652F] dark:text-[#339B56] border-b-2 border-[#14652F] dark:border-[#2E9250]">Conversation</span><span class="pb-3 text-[13px] font-semibold text-[#8A857A] dark:text-[#868778]">{{ $isFr?'Activité':'Activity' }}</span></div>
                        <div class="mt-4 space-y-5">
                            @foreach($replies as $r)
                            @php $staff = (bool)$r->is_staff; @endphp
                            <div class="flex gap-3">
                                <span class="w-9 h-9 rounded-full {{ $staff?'bg-[#C9942E] dark:bg-[#3A2B06] ':'bg-[#14652F] dark:bg-[#2E9250] ' }} text-white text-[14px] md:text-[12px] font-bold flex items-center justify-center shrink-0">{{ mb_strtoupper(mb_substr($r->author_name ?? 'U',0,2)) }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="flex flex-wrap items-center gap-2 text-[13px]"><b class="text-[#1B1B18] dark:text-[#F3EFE7]">{{ $r->author_name ?? ($staff?'Agent':'Client') }}</b><span class="rounded px-1.5 py-0.5 text-[12px] md:text-[10px] font-semibold {{ $staff?'bg-[#FDF3E0] dark:bg-[#3A2B06] text-[#C97A16] dark:text-[#EDB33A] ':'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56] ' }}">{{ $staff?'Agent':'Client' }}</span><span class="ml-auto text-[13px] md:text-[11px] text-[#8A857A] dark:text-[#868778]">{{ $dtf($r->created_at) }}</span></p>
                                    <div class="mt-1.5 text-[14px] md:text-[12.5px] text-[#3B382F] dark:text-[#B4B5A6] leading-relaxed whitespace-pre-line">{{ $isFr ? $r->body_fr : ($r->body_en ?? $r->body_fr) }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Reply form --}}
                        <form method="POST" action="{{ route('admin.support.reply', ['id'=>$ticket->id]) }}" id="reply" class="mt-6 border-t border-[#EFEBE2] dark:border-[#262B21] pt-4">
                            @csrf<input type="hidden" name="lang" value="{{ $lang }}">
                            <div class="flex items-center gap-6 mb-3"><span class="text-[13px] font-semibold text-[#14652F] dark:text-[#339B56] border-b-2 border-[#14652F] dark:border-[#2E9250] pb-1">{{ $isFr?'Réponse':'Reply' }}</span><span class="text-[13px] font-semibold text-[#8A857A] dark:text-[#868778]">{{ $isFr?'Note interne':'Internal note' }}</span></div>
                            @error('body')<p class="mb-2 text-[14px] md:text-[12px] text-[#B42025] dark:text-[#F0555C]">{{ $message }}</p>@enderror
                            {{-- The toolbar sits above the field rather than inside a second border,
                                 so the editor keeps the same box as every other field. --}}
                            <div>
                                <div class="flex items-center gap-1 mb-1.5 text-[#8A857A] dark:text-[#868778]">@foreach(['bold','italic','underline','list','link','image'] as $tb)<span class="w-7 h-7 rounded flex items-center justify-center"><i data-lucide="{{ $tb }}" class="w-3.5 h-3.5"></i></span>@endforeach</div>
                                <textarea name="body" rows="4" required placeholder="{{ $isFr?'Écrire votre réponse...':'Write your reply...' }}" class="ui-field ui-textarea"></textarea>
                            </div>
                            <div class="mt-3 flex items-center justify-end"><button type="submit" class="ui-btn ui-btn-primary"><i data-lucide="reply" class="w-4 h-4"></i>{{ $isFr?'Envoyer la réponse':'Send reply' }}</button></div>
                        </form>
                    </section>
                </div>

                {{-- Right rail --}}
                <aside class="space-y-4">
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Informations du ticket':'Ticket information' }}</h2>
                        <dl class="mt-3 space-y-2.5 text-[14px] md:text-[12px]">
                            <div class="flex items-center justify-between"><dt class="text-[#6F6B60] dark:text-[#868778]">ID Ticket</dt><dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $ref }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="text-[#6F6B60] dark:text-[#868778]">{{ $isFr?'Créé le':'Created' }}</dt><dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $dtf($ticket->created_at) }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="text-[#6F6B60] dark:text-[#868778]">{{ $isFr?'Dernière MàJ':'Last update' }}</dt><dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $dtf($ticket->updated_at) }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="text-[#6F6B60] dark:text-[#868778]">Statut</dt><dd class="font-semibold" style="color:{{ $stColor }}">{{ $stLabel }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="text-[#6F6B60] dark:text-[#868778]">{{ $isFr?'Priorité':'Priority' }}</dt><dd class="font-semibold" style="color:{{ $prColor }}">{{ $prLabel }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="text-[#6F6B60] dark:text-[#868778]">Canal</dt><dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">Email</dd></div>
                        </dl>
                    </section>
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Informations du demandeur':'Requester information' }}</h2>
                        <div class="mt-3 flex items-center gap-3"><span class="w-10 h-10 rounded-full bg-[#14652F] dark:bg-[#2E9250] text-white dark:text-[#04150A] text-[13px] font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($ticket->user_name ?? 'U',0,2)) }}</span><p class="text-[13px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $ticket->user_name ?? 'Client' }}</p></div>
                        <dl class="mt-3 space-y-2 text-[14px] md:text-[12px] text-[#3B382F] dark:text-[#B4B5A6]">
                            <p class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4 text-[#8A857A] dark:text-[#868778]"></i>{{ $ticket->user_email ?? '—' }}</p>
                            <p class="flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4 text-[#8A857A] dark:text-[#868778]"></i>{{ $ticket->user_phone ?? '—' }}</p>
                            <p class="flex items-center gap-2"><i data-lucide="calendar" class="w-4 h-4 text-[#8A857A] dark:text-[#868778]"></i>{{ $isFr?'Membre depuis':'Member since' }} {{ $ticket->user_since ? \Carbon\Carbon::parse($ticket->user_since)->format('d M Y') : '—' }}</p>
                        </dl>
                        <a href="{{ route('admin.users', ['lang'=>$lang]) }}" class="mt-3 flex items-center justify-center min-h-[44px] md:min-h-0 text-center border border-[#EAE5D8] dark:border-[#262B21] hover:border-[#14652F] dark:hover:border-[#2E9250] rounded-lg py-2 text-[14px] md:text-[12px] font-semibold text-[#3B382F] dark:text-[#B4B5A6]">{{ $isFr?'Voir le profil':'View profile' }}</a>
                    </section>
                </aside>
            </div>
            <p class="mt-6 text-center text-[13px] md:text-[11.5px] text-[#8A857A] dark:text-[#868778]">© {{ now()->year }} {{ $isFr ? 'Artisan Hub 237. Tous droits réservés.' : 'Artisan Hub 237. All rights reserved.' }}</p>
@endsection
