@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'notifications';
    $pageTitle = $isFr?'DÉTAIL DE LA NOTIFICATION':'NOTIFICATION DETAIL';
    $pageBreadcrumb = [['Accueil', route('dashboard.admin')],['Notifications', route('admin.notifications')],[$isFr?'Détail':'Detail', null]];
    $pageSearchPlaceholder = $isFr?'Rechercher une notification...':'Search a notification...';
    $ref = '#NTF-' . \Carbon\Carbon::parse($notification->created_at)->format('Y') . '-' . str_pad((string)$notification->id, 5, '0', STR_PAD_LEFT);
    $dtf = fn($v)=> $v ? \Carbon\Carbon::parse($v)->format('d M Y, H:i') : '—';
    // Only columns that exist on the row. There is no author column on
    // user_notifications, so "created by" is not shown at all.
    $infos = [
        ['ID Notification', $ref],
        ['Type', $notification->type],
        [$isFr?'Date de création':'Created', $dtf($notification->created_at)],
        [$isFr?'Dernière mise à jour':'Last update', $dtf($notification->updated_at)],
    ];
    $audit = array_values(array_filter([
        [$isFr?'Créée':'Created', $dtf($notification->created_at)],
        $notification->read_at ? [$isFr?'Lue':'Read', $dtf($notification->read_at)] : null,
        [$isFr?'Dernière mise à jour':'Last update', $dtf($notification->updated_at)],
    ]));
    $isRead = (bool) $notification->read_at;
@endphp

@section('content')
            <a href="{{ route('admin.notifications', ['lang'=>$lang]) }}" class="ui-btn ui-btn-secondary"><i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr?'Retour':'Back' }}</a>

            <div class="mt-4 grid grid-cols-1 2xl:grid-cols-[1fr_320px] gap-5 items-start">
                <div class="space-y-5">
                    {{-- Header card --}}
                    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0E3D22] dark:from-[#0C3B1E] to-[#12522C] dark:to-[#2E9250] px-6 py-5">
                        <span class="inline-block rounded-md px-2.5 py-0.5 text-[11px] font-semibold bg-[#E9C25A] dark:bg-[#3A2B06]/20 text-[#E9C25A]">{{ $isFr?'Envoyée':'Sent' }}</span>
                        <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
                            <div class="flex items-center gap-3"><span class="w-12 h-12 rounded-xl bg-white dark:bg-[#12150F]/10 flex items-center justify-center"><i data-lucide="bell" class="w-6 h-6 text-[#E9C25A]"></i></span><div><h1 class="text-[19px] font-bold text-white">{{ $notification->title }}</h1><p class="text-[12.5px] text-[#CFE3D5]">{{ $notification->body }}</p></div></div>
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex items-center gap-2 bg-white dark:bg-[#12150F]/10 rounded-lg px-3.5 h-[36px] text-[12px] font-semibold text-white"><i data-lucide="settings" class="w-4 h-4"></i>Actions</span>
                                <a href="{{ route('admin.cms', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-white dark:bg-[#12150F]/10 hover:bg-white dark:hover:bg-[#242A1E]/15 rounded-lg px-3.5 h-[36px] text-[12px] font-semibold text-white"><i data-lucide="send" class="w-4 h-4"></i>{{ $isFr?'Renvoyer':'Resend' }}</a>
                            </div>
                        </div>
                        <p class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1 text-[11.5px] text-[#CFE3D5]"><span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5"></i>{{ $dtf($notification->created_at) }}</span>@if($recipient)<span class="flex items-center gap-1.5"><i data-lucide="user" class="w-3.5 h-3.5"></i>{{ $isFr?'Destinataire':'Recipient' }} : {{ $recipient->name }}</span>@endif<span class="flex items-center gap-1.5"><i data-lucide="bell" class="w-3.5 h-3.5"></i>{{ $isFr?'Canal : Notification in-app':'Channel: in-app notification' }}</span><span class="flex items-center gap-1.5"><i data-lucide="hash" class="w-3.5 h-3.5"></i>{{ $ref }}</span></p>
                    </section>

                    {{-- Content --}}
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Contenu de la notification':'Notification content' }}</h2>
                        <dl class="mt-4 space-y-3 text-[12.5px]">
                            <div class="flex gap-4"><dt class="w-28 shrink-0 text-[#6F6B60] dark:text-[#868778]">{{ $isFr?'Titre':'Title' }} :</dt><dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $notification->title }}</dd></div>
                            <div class="flex gap-4"><dt class="w-28 shrink-0 text-[#6F6B60] dark:text-[#868778]">Message :</dt><dd class="text-[#3B382F] dark:text-[#B4B5A6]">{{ $notification->body }}</dd></div>
                            @if($notification->link)
                            <div class="flex gap-4"><dt class="w-28 shrink-0 text-[#6F6B60] dark:text-[#868778]">{{ $isFr?'Bouton d\'action':'Action button' }} :</dt><dd><a href="{{ $notification->link }}" class="ui-btn ui-btn-primary ui-btn-sm">{{ $isFr?'Voir':'View' }}</a></dd></div>
                            <div class="flex gap-4"><dt class="w-28 shrink-0 text-[#6F6B60] dark:text-[#868778]">{{ $isFr?'Lien':'Link' }} :</dt><dd><a href="{{ $notification->link }}" class="text-[12px] text-[#3565DE] dark:text-[#8FB6F5] underline break-all">{{ url($notification->link) }}</a></dd></div>
                            @endif
                        </dl>
                    </section>

                    {{-- Email preview --}}
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Aperçu de l\'email':'Email preview' }}</h2>
                        <div class="mt-4 border border-[#EFEBE2] dark:border-[#262B21] rounded-xl overflow-hidden">
                            <div class="bg-gradient-to-r from-[#0E3D22] dark:from-[#0C3B1E] to-[#12522C] dark:to-[#2E9250] px-6 py-4 flex items-center gap-3"><img src="{{ brand_asset('mark') }}" alt="" class="w-10 h-10 object-contain"><div><p class="text-[11px] font-bold text-white uppercase leading-tight">Artisan Hub 237<br>de l'Artisanat du Cameroun</p></div></div>
                            <div class="px-6 py-5"><p class="text-[13px] text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr?'Bonjour,':'Hello,' }}</p><p class="mt-2 text-[12.5px] text-[#3B382F] dark:text-[#B4B5A6]">{{ $notification->body }}</p><div class="mt-4 flex items-center justify-between bg-[#F7F8F7] dark:bg-[#12150F] rounded-lg px-4 py-3"><span class="text-[11.5px] text-[#6F6B60] dark:text-[#868778]">{{ $notification->title }}</span>@if($notification->link)<a href="{{ $notification->link }}" class="bg-[#0F4824] dark:bg-[#2E9250] text-white dark:text-[#04150A] rounded-lg px-3.5 py-1.5 text-[11.5px] font-semibold">{{ $isFr?'Voir':'View' }}</a>@endif</div><p class="mt-4 text-[12px] text-[#3B382F] dark:text-[#B4B5A6]">{{ $isFr?'Merci,':'Thank you,' }}<br>{{ $isFr?'Équipe Support – Artisan Hub 237':'Support Team – Artisan Hub 237' }}</p></div>
                        </div>
                    </section>
                </div>

                <aside class="space-y-4">
                    <section class="ui-card">
                        {{-- The design's sent/pending/failed breakdown had no source: nothing
                             links an in-app notification to a per-channel send log. What the row
                             really records is who received it and whether they have read it. --}}
                        <h2 class="ui-card-title">{{ $isFr?'Destinataire':'Recipient' }}</h2>
                        <dl class="mt-3 space-y-2.5 text-[12px]">
                            @if($recipient)
                            <div class="flex items-center justify-between gap-3"><dt class="text-[#6F6B60] dark:text-[#868778]">{{ $isFr?'Utilisateur':'User' }}</dt><dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7] text-right">{{ $recipient->name }}</dd></div>
                            <div class="flex items-center justify-between gap-3"><dt class="text-[#6F6B60] dark:text-[#868778]">Email</dt><dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7] text-right break-all">{{ $recipient->email }}</dd></div>
                            @endif
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[#6F6B60] dark:text-[#868778]">{{ $isFr?'Statut':'Status' }}</dt>
                                <dd class="text-right"><span class="inline-block rounded-md px-2.5 py-0.5 text-[11px] font-semibold {{ $isRead ? 'bg-[#E4F1E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56] ' : 'bg-[#FBF1DD] dark:bg-[#3A2B06] text-[#C9942E] dark:text-[#EDB33A] ' }}">{{ $isRead ? ($isFr?'Lue':'Read') : ($isFr?'Non lue':'Unread') }}</span></dd>
                            </div>
                            @if($isRead)
                            <div class="flex items-center justify-between gap-3"><dt class="text-[#6F6B60] dark:text-[#868778]">{{ $isFr?'Lue le':'Read on' }}</dt><dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7] text-right">{{ $dtf($notification->read_at) }}</dd></div>
                            @endif
                        </dl>
                    </section>
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Informations de la notification':'Notification information' }}</h2>
                        <dl class="mt-3 space-y-2.5 text-[12px]">@foreach($infos as [$l,$v])<div class="flex items-center justify-between gap-3"><dt class="text-[#6F6B60] dark:text-[#868778]">{{ $l }}</dt><dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7] text-right">{{ $v }}</dd></div>@endforeach</dl>
                    </section>
                    <section class="ui-card">
                        <h2 class="ui-card-title">Audit</h2>
                        <div class="mt-3 space-y-3">@foreach($audit as [$aE,$aW])<div class="flex gap-3"><span class="w-7 h-7 rounded-full bg-[#F3F0E6] dark:bg-[#1A1E16] flex items-center justify-center shrink-0"><i data-lucide="clock" class="w-3.5 h-3.5 text-[#14652F] dark:text-[#339B56]"></i></span><div><p class="text-[12px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $aE }}</p><p class="text-[10.5px] text-[#8A857A] dark:text-[#868778]">{{ $aW }}</p></div></div>@endforeach</div>
                    </section>
                </aside>
            </div>
            <p class="mt-6 text-center text-[11.5px] text-[#8A857A] dark:text-[#868778]">© {{ now()->year }} {{ $isFr ? 'Artisan Hub 237. Tous droits réservés.' : 'Artisan Hub 237. All rights reserved.' }}</p>
@endsection
