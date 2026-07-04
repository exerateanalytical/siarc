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
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $ref }} — Support</title>
<script src="{{ asset('vendor/tailwindcss.js') }}"></script>
<script>tailwind.config={theme:{extend:{colors:{leaf:'#14652F'},fontFamily:{sans:['Poppins','system-ui','sans-serif']}}}}</script>
<script src="{{ asset('vendor/lucide.min.js') }}"></script><link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
<style>body{font-family:'Poppins',system-ui,sans-serif}html,body{overflow-x:clip}#ad-sidebar{display:none}#ad-sidebar.ad-open{display:flex;position:fixed;inset:0 auto 0 0;width:270px;z-index:60;overflow-y:auto}@media(min-width:1024px){#ad-sidebar,#ad-sidebar.ad-open{display:flex;position:sticky;top:0;height:100vh;width:250px}}</style></head>
<body class="bg-[#F8F4EC] text-[#1B1B18] antialiased">
<img src="{{ asset('images/landing/ad-kente-top.png') }}" alt="" class="w-full h-[8px] object-cover" aria-hidden="true">
<div class="flex items-stretch min-h-screen">
    @include('pages.partials.admin-sidebar')
    <div class="flex-1 min-w-0">
        @include('pages.partials.admin-heritage-header', ['pageTitle' => $isFr?'DÉTAILS DU TICKET':'TICKET DETAILS', 'pageBreadcrumb'=>[['Accueil', route('dashboard.admin')],['Tickets', route('admin.support')],[$ref, null]], 'pageSearchPlaceholder'=>$isFr?'Rechercher un ticket, un utilisateur...':'Search a ticket, a user...'])
        <main class="px-5 lg:px-7 pt-5 pb-8">
            @if(session('success'))<div class="mb-4 bg-[#E2F3E8] border border-[#BFDCC8] rounded-xl px-4 py-3 flex items-center gap-3 text-[13px] text-[#14532D]"><i data-lucide="circle-check" class="w-4 h-4 shrink-0 text-[#157A43]"></i>{{ session('success') }}</div>@endif

            <div class="grid grid-cols-1 2xl:grid-cols-[1fr_320px] gap-5 items-start">
                <div class="space-y-5">
                    {{-- Ticket header --}}
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <span class="w-[54px] h-[54px] rounded-xl bg-[#E8F2EC] flex items-center justify-center shrink-0"><i data-lucide="file-text" class="w-6 h-6 text-[#157A43]"></i></span>
                                <div>
                                    <span class="inline-block rounded-md px-2.5 py-0.5 text-[11px] font-semibold" style="background-color: {{ $stColor }}1a;color: {{ $stColor }}">{{ $stLabel }}</span>
                                    <p class="mt-1 text-[15px] font-bold text-[#1B1B18]">{{ $ref }}</p>
                                    <p class="text-[16px] font-bold text-[#1B1B18]">{{ $subj }}</p>
                                    <p class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11.5px] text-[#6F6B60]"><span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5"></i>{{ $dtf($ticket->created_at) }}</span><span class="flex items-center gap-1.5"><i data-lucide="user" class="w-3.5 h-3.5"></i>{{ $isFr?'Par':'By' }} {{ $ticket->user_name ?? 'Client' }}</span><span class="flex items-center gap-1.5"><i data-lucide="mail" class="w-3.5 h-3.5"></i>via Email</span></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <a href="{{ route('admin.support', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-white border border-[#E9E4D8] hover:border-[#14652F] rounded-lg px-3.5 h-[38px] text-[12px] font-semibold text-[#3B382F]"><i data-lucide="settings" class="w-4 h-4"></i>Actions</a>
                                <a href="#reply" class="inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-white"><i data-lucide="reply" class="w-4 h-4"></i>{{ $isFr?'Répondre':'Reply' }}</a>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 border-t border-[#F0F1F0] pt-4">
                            @foreach($metaRow as [$mI,$mL,$mV])<div class="flex items-start gap-2"><i data-lucide="{{ $mI }}" class="w-4 h-4 mt-0.5 text-[#C9942E] shrink-0"></i><div><p class="text-[10px] text-[#8A857A]">{{ $mL }}</p><p class="text-[12px] font-semibold text-[#1B1B18]">{{ $mV }}</p></div></div>@endforeach
                        </div>
                    </section>

                    {{-- Conversation --}}
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                        <div class="flex items-center gap-6 border-b border-[#EAE7DE]"><span class="pb-3 text-[13px] font-semibold text-[#14652F] border-b-2 border-[#14652F]">Conversation</span><span class="pb-3 text-[13px] font-semibold text-[#8A857A]">{{ $isFr?'Activité':'Activity' }}</span></div>
                        <div class="mt-4 space-y-5">
                            @foreach($replies as $r)
                            @php $staff = (bool)$r->is_staff; @endphp
                            <div class="flex gap-3">
                                <span class="w-9 h-9 rounded-full {{ $staff?'bg-[#C9942E]':'bg-[#14652F]' }} text-white text-[12px] font-bold flex items-center justify-center shrink-0">{{ mb_strtoupper(mb_substr($r->author_name ?? 'U',0,2)) }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="flex flex-wrap items-center gap-2 text-[13px]"><b class="text-[#1B1B18]">{{ $r->author_name ?? ($staff?'Agent':'Client') }}</b><span class="rounded px-1.5 py-0.5 text-[10px] font-semibold {{ $staff?'bg-[#FDF3E0] text-[#C97A16]':'bg-[#E2F3E8] text-[#157A43]' }}">{{ $staff?'Agent':'Client' }}</span><span class="ml-auto text-[11px] text-[#8A857A]">{{ $dtf($r->created_at) }}</span></p>
                                    <div class="mt-1.5 text-[12.5px] text-[#3B382F] leading-relaxed whitespace-pre-line">{{ $isFr ? $r->body_fr : ($r->body_en ?? $r->body_fr) }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Reply form --}}
                        <form method="POST" action="{{ route('admin.support.reply', ['id'=>$ticket->id]) }}" id="reply" class="mt-6 border-t border-[#F0F1F0] pt-4">
                            @csrf<input type="hidden" name="lang" value="{{ $lang }}">
                            <div class="flex items-center gap-6 mb-3"><span class="text-[13px] font-semibold text-[#14652F] border-b-2 border-[#14652F] pb-1">{{ $isFr?'Réponse':'Reply' }}</span><span class="text-[13px] font-semibold text-[#8A857A]">{{ $isFr?'Note interne':'Internal note' }}</span></div>
                            @error('body')<p class="mb-2 text-[12px] text-[#B42025]">{{ $message }}</p>@enderror
                            <div class="border border-[#E5E3E0] rounded-lg overflow-hidden">
                                <div class="flex items-center gap-1 border-b border-[#F0EFEA] px-2 py-1.5 text-[#8A857A]">@foreach(['bold','italic','underline','list','link','image'] as $tb)<span class="w-7 h-7 rounded flex items-center justify-center"><i data-lucide="{{ $tb }}" class="w-3.5 h-3.5"></i></span>@endforeach</div>
                                <textarea name="body" rows="4" required placeholder="{{ $isFr?'Écrire votre réponse...':'Write your reply...' }}" class="w-full px-3.5 py-3 text-[13px] focus:outline-none resize-y"></textarea>
                            </div>
                            <div class="mt-3 flex items-center justify-end"><button type="submit" class="inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-5 h-[40px] text-[12.5px] font-semibold text-white"><i data-lucide="reply" class="w-4 h-4"></i>{{ $isFr?'Envoyer la réponse':'Send reply' }}</button></div>
                        </form>
                    </section>
                </div>

                {{-- Right rail --}}
                <aside class="space-y-4">
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr?'Informations du ticket':'Ticket information' }}</h2>
                        <dl class="mt-3 space-y-2.5 text-[12px]">
                            <div class="flex items-center justify-between"><dt class="text-[#6F6B60]">ID Ticket</dt><dd class="font-semibold text-[#1B1B18]">{{ $ref }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="text-[#6F6B60]">{{ $isFr?'Créé le':'Created' }}</dt><dd class="font-semibold text-[#1B1B18]">{{ $dtf($ticket->created_at) }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="text-[#6F6B60]">{{ $isFr?'Dernière MàJ':'Last update' }}</dt><dd class="font-semibold text-[#1B1B18]">{{ $dtf($ticket->updated_at) }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="text-[#6F6B60]">Statut</dt><dd class="font-semibold" style="color:{{ $stColor }}">{{ $stLabel }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="text-[#6F6B60]">{{ $isFr?'Priorité':'Priority' }}</dt><dd class="font-semibold" style="color:{{ $prColor }}">{{ $prLabel }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="text-[#6F6B60]">Canal</dt><dd class="font-semibold text-[#1B1B18]">Email</dd></div>
                        </dl>
                    </section>
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr?'Informations du demandeur':'Requester information' }}</h2>
                        <div class="mt-3 flex items-center gap-3"><span class="w-10 h-10 rounded-full bg-[#14652F] text-white text-[13px] font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($ticket->user_name ?? 'U',0,2)) }}</span><p class="text-[13px] font-bold text-[#1B1B18]">{{ $ticket->user_name ?? 'Client' }}</p></div>
                        <dl class="mt-3 space-y-2 text-[12px] text-[#3B382F]">
                            <p class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4 text-[#8A857A]"></i>{{ $ticket->user_email ?? '—' }}</p>
                            <p class="flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4 text-[#8A857A]"></i>{{ $ticket->user_phone ?? '—' }}</p>
                            <p class="flex items-center gap-2"><i data-lucide="calendar" class="w-4 h-4 text-[#8A857A]"></i>{{ $isFr?'Membre depuis':'Member since' }} {{ $ticket->user_since ? \Carbon\Carbon::parse($ticket->user_since)->format('d M Y') : '—' }}</p>
                        </dl>
                        <a href="{{ route('admin.users', ['lang'=>$lang]) }}" class="mt-3 block text-center border border-[#E5E7E5] hover:border-[#14652F] rounded-lg py-2 text-[12px] font-semibold text-[#3B382F]">{{ $isFr?'Voir le profil':'View profile' }}</a>
                    </section>
                </aside>
            </div>
            <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">© {{ now()->year }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>
        </main>
    </div>
</div>
<script>lucide.createIcons();</script></body></html>
