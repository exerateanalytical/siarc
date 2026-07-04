@php
    $isFr = $lang === 'fr';
    $adminActive = 'notifications';
    $ref = '#NTF-' . \Carbon\Carbon::parse($notification->created_at)->format('Y') . '-' . str_pad((string)$notification->id, 5, '0', STR_PAD_LEFT);
    $dtf = fn($v)=> $v ? \Carbon\Carbon::parse($v)->format('d M Y, H:i') : '—';
    $infos = [
        ['ID Notification', $ref],
        ['Type', $isFr?'Alerte système':'System alert'],
        [$isFr?'Créée par':'Created by', 'Admin Super'],
        [$isFr?'Date de création':'Created', $dtf($notification->created_at)],
        [$isFr?'Dernière mise à jour':'Last update', $dtf($notification->updated_at)],
    ];
    $audit = [
        [$isFr?'Créée':'Created', $dtf($notification->created_at), 'Admin Super'],
        [$isFr?'Envoyée':'Sent', $dtf($notification->created_at), $isFr?'Système':'System'],
        [$isFr?'Dernière mise à jour':'Last update', $dtf($notification->updated_at), 'Admin Super'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $notification->title }} — Notification</title>
<script src="{{ asset('vendor/tailwindcss.js') }}"></script>
<script>tailwind.config={theme:{extend:{colors:{leaf:'#14652F'},fontFamily:{sans:['Poppins','system-ui','sans-serif']}}}}</script>
<script src="{{ asset('vendor/lucide.min.js') }}"></script><link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
<style>body{font-family:'Poppins',system-ui,sans-serif}html,body{overflow-x:clip}#ad-sidebar{display:none}#ad-sidebar.ad-open{display:flex;position:fixed;inset:0 auto 0 0;width:270px;z-index:60;overflow-y:auto}@media(min-width:1024px){#ad-sidebar,#ad-sidebar.ad-open{display:flex;position:sticky;top:0;height:100vh;width:250px}}</style></head>
<body class="bg-[#F8F4EC] text-[#1B1B18] antialiased">
<img src="{{ asset('images/landing/ad-kente-top.png') }}" alt="" class="w-full h-[8px] object-cover" aria-hidden="true">
<div class="flex items-stretch min-h-screen">
    @include('pages.partials.admin-sidebar')
    <div class="flex-1 min-w-0">
        @include('pages.partials.admin-heritage-header', ['pageTitle' => $isFr?'DÉTAIL DE LA NOTIFICATION':'NOTIFICATION DETAIL', 'pageBreadcrumb'=>[['Accueil', route('dashboard.admin')],['Notifications', route('admin.notifications')],[$isFr?'Détail':'Detail', null]], 'pageSearchPlaceholder'=>$isFr?'Rechercher une notification...':'Search a notification...'])
        <main class="px-5 lg:px-7 pt-5 pb-8">
            <a href="{{ route('admin.notifications', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-white border border-[#E9E4D8] hover:border-[#14652F] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-[#3B382F]"><i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr?'Retour':'Back' }}</a>

            <div class="mt-4 grid grid-cols-1 2xl:grid-cols-[1fr_320px] gap-5 items-start">
                <div class="space-y-5">
                    {{-- Header card --}}
                    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0E3D22] to-[#12522C] px-6 py-5">
                        <span class="inline-block rounded-md px-2.5 py-0.5 text-[11px] font-semibold bg-[#E9C25A]/20 text-[#E9C25A]">{{ $isFr?'Envoyée':'Sent' }}</span>
                        <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
                            <div class="flex items-center gap-3"><span class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center"><i data-lucide="bell" class="w-6 h-6 text-[#E9C25A]"></i></span><div><h1 class="text-[19px] font-bold text-white">{{ $notification->title }}</h1><p class="text-[12.5px] text-[#CFE3D5]">{{ $notification->body }}</p></div></div>
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex items-center gap-2 bg-white/10 rounded-lg px-3.5 h-[36px] text-[12px] font-semibold text-white"><i data-lucide="settings" class="w-4 h-4"></i>Actions</span>
                                <a href="{{ route('admin.cms', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/15 rounded-lg px-3.5 h-[36px] text-[12px] font-semibold text-white"><i data-lucide="send" class="w-4 h-4"></i>{{ $isFr?'Renvoyer':'Resend' }}</a>
                            </div>
                        </div>
                        <p class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1 text-[11.5px] text-[#CFE3D5]"><span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5"></i>{{ $dtf($notification->created_at) }}</span><span class="flex items-center gap-1.5"><i data-lucide="user" class="w-3.5 h-3.5"></i>{{ $isFr?'Envoyée par':'Sent by' }} : Admin Super</span><span class="flex items-center gap-1.5"><i data-lucide="mail" class="w-3.5 h-3.5"></i>Canal : Email</span><span class="flex items-center gap-1.5"><i data-lucide="hash" class="w-3.5 h-3.5"></i>{{ $ref }}</span></p>
                    </section>

                    {{-- Content --}}
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                        <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr?'Contenu de la notification':'Notification content' }}</h2>
                        <dl class="mt-4 space-y-3 text-[12.5px]">
                            <div class="flex gap-4"><dt class="w-28 shrink-0 text-[#6F6B60]">{{ $isFr?'Titre':'Title' }} :</dt><dd class="font-semibold text-[#1B1B18]">{{ $notification->title }}</dd></div>
                            <div class="flex gap-4"><dt class="w-28 shrink-0 text-[#6F6B60]">Message :</dt><dd class="text-[#3B382F]">{{ $notification->body }}</dd></div>
                            @if($notification->link)
                            <div class="flex gap-4"><dt class="w-28 shrink-0 text-[#6F6B60]">{{ $isFr?'Bouton d\'action':'Action button' }} :</dt><dd><a href="{{ $notification->link }}" class="inline-flex items-center gap-1.5 bg-[#0F4824] text-white rounded-lg px-3.5 py-1.5 text-[12px] font-semibold">{{ $isFr?'Voir':'View' }}</a></dd></div>
                            <div class="flex gap-4"><dt class="w-28 shrink-0 text-[#6F6B60]">{{ $isFr?'Lien':'Link' }} :</dt><dd><a href="{{ $notification->link }}" class="text-[12px] text-[#3565DE] underline break-all">{{ url($notification->link) }}</a></dd></div>
                            @endif
                        </dl>
                    </section>

                    {{-- Email preview --}}
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                        <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr?'Aperçu de l\'email':'Email preview' }}</h2>
                        <div class="mt-4 border border-[#EFF0EF] rounded-xl overflow-hidden">
                            <div class="bg-gradient-to-r from-[#0E3D22] to-[#12522C] px-6 py-4 flex items-center gap-3"><img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-9 h-10 object-contain"><div><p class="text-[11px] font-bold text-white uppercase leading-tight">Galerie Virtuelle Nationale<br>de l'Artisanat du Cameroun</p></div></div>
                            <div class="px-6 py-5"><p class="text-[13px] text-[#1B1B18]">{{ $isFr?'Bonjour,':'Hello,' }}</p><p class="mt-2 text-[12.5px] text-[#3B382F]">{{ $notification->body }}</p><div class="mt-4 flex items-center justify-between bg-[#F7F8F7] rounded-lg px-4 py-3"><span class="text-[11.5px] text-[#6F6B60]">{{ $notification->title }}</span>@if($notification->link)<a href="{{ $notification->link }}" class="bg-[#0F4824] text-white rounded-lg px-3.5 py-1.5 text-[11.5px] font-semibold">{{ $isFr?'Voir':'View' }}</a>@endif</div><p class="mt-4 text-[12px] text-[#3B382F]">{{ $isFr?'Merci,':'Thank you,' }}<br>{{ $isFr?'Équipe Support – Galerie Virtuelle':'Support Team – Virtual Gallery' }}</p></div>
                        </div>
                    </section>
                </div>

                <aside class="space-y-4">
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr?'Statistiques d\'envoi':'Delivery stats' }}</h2>
                        <div class="mt-4 flex items-center gap-4">
                            <span class="relative w-[86px] h-[86px] rounded-full shrink-0" style="background:conic-gradient(#157A43 0deg 360deg)"><span class="absolute inset-[13px] rounded-full bg-white flex flex-col items-center justify-center"><span class="text-[16px] font-bold text-[#1B1B18] leading-none">1</span><span class="text-[8.5px] text-[#8A857A]">{{ $isFr?'Destinataire':'Recipient' }}</span></span></span>
                            <div class="flex-1 space-y-1.5 text-[11.5px]"><div class="flex items-center justify-between"><span class="flex items-center gap-1.5 text-[#3B382F]"><span class="w-2 h-2 rounded-full bg-[#157A43]"></span>{{ $isFr?'Envoyés':'Sent' }}</span><span class="font-semibold">1 (100%)</span></div><div class="flex items-center justify-between"><span class="flex items-center gap-1.5 text-[#3B382F]"><span class="w-2 h-2 rounded-full bg-[#C9942E]"></span>{{ $isFr?'En attente':'Pending' }}</span><span class="font-semibold">0 (0%)</span></div><div class="flex items-center justify-between"><span class="flex items-center gap-1.5 text-[#3B382F]"><span class="w-2 h-2 rounded-full bg-[#DC2626]"></span>{{ $isFr?'Échecs':'Failed' }}</span><span class="font-semibold">0 (0%)</span></div></div>
                        </div>
                        <div class="mt-3"><div class="flex items-center justify-between text-[11.5px]"><span class="text-[#6F6B60]">{{ $isFr?'Taux de livraison':'Delivery rate' }}</span><span class="font-semibold text-[#157A43]">100%</span></div><div class="mt-1 h-2 rounded-full bg-[#F0EFEA] overflow-hidden"><span class="block h-full rounded-full bg-[#157A43]" style="width:100%"></span></div></div>
                    </section>
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr?'Informations de la notification':'Notification information' }}</h2>
                        <dl class="mt-3 space-y-2.5 text-[12px]">@foreach($infos as [$l,$v])<div class="flex items-center justify-between gap-3"><dt class="text-[#6F6B60]">{{ $l }}</dt><dd class="font-semibold text-[#1B1B18] text-right">{{ $v }}</dd></div>@endforeach</dl>
                    </section>
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">Audit</h2>
                        <div class="mt-3 space-y-3">@foreach($audit as [$aE,$aW,$aB])<div class="flex gap-3"><span class="w-7 h-7 rounded-full bg-[#F3F0E6] flex items-center justify-center shrink-0"><i data-lucide="clock" class="w-3.5 h-3.5 text-[#14652F]"></i></span><div><p class="text-[12px] font-semibold text-[#1B1B18]">{{ $aE }}</p><p class="text-[10.5px] text-[#8A857A]">{{ $aW }} · {{ $isFr?'par':'by' }} {{ $aB }}</p></div></div>@endforeach</div>
                    </section>
                </aside>
            </div>
            <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">© {{ now()->year }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>
        </main>
    </div>
</div>
<script>lucide.createIcons();</script></body></html>
