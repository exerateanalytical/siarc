@php
    // ══ RFID Write Data Flow — verbatim from the approved design ══
    $rfidHref = route('siarc.admin.accred.rfid', ['lang' => $lang]);
@endphp

{{-- ══ TITLE ROW ══ --}}
<div class="mb-5">
    <h1 class="text-[26px] font-bold text-[#131313] tracking-tight">RFID Write Data Flow</h1>
    <p class="text-[12px] text-[#8A857A] mt-1 flex items-center gap-1.5">Accréditation <i data-lucide="chevron-right" class="w-3 h-3"></i> <a href="{{ $rfidHref }}" class="hover:text-[#3B382F]">RFID Support</a> <i data-lucide="chevron-right" class="w-3 h-3"></i> <span class="text-[#3B382F]">Write Data Flow</span></p>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-5 items-start">
    <div>
        {{-- ══ STEPPER ══ --}}
        <div class="flex flex-wrap items-center gap-2 mb-4">
            @foreach([['1','Sélection','Choisir la carte',true],['2','Données','Configurer les données',false],['3','Écriture','Écrire sur la carte',false],['4','Vérification','Vérifier les données',false],['5','Terminé','Opération réussie',false]] as [$n,$t,$s,$on])
            <span class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-full flex items-center justify-center text-[13px] font-bold {{ $on ? 'bg-[#157A43] text-white' : 'bg-white border border-[#E6E3DB] text-[#8A857A]' }}">{{ $n }}</span>
                <span class="leading-tight">
                    <span class="block text-[13px] font-semibold {{ $on ? 'text-[#157A43]' : 'text-[#3B382F]' }}">{{ $t }}</span>
                    <span class="block text-[10.5px] text-[#B0AB9F]">{{ $s }}</span>
                </span>
            </span>
            @unless($loop->last)<i data-lucide="arrow-right" class="w-4 h-4 text-[#C9C5BA] mx-1"></i>@endunless
            @endforeach
        </div>

        {{-- info banner --}}
        <div class="rounded-lg bg-[#EAF6EE] border border-[#CFE8D8] px-4 py-2.5 mb-5 flex items-center gap-2.5">
            <i data-lucide="info" class="w-4 h-4 text-[#157A43] shrink-0"></i>
            <p class="text-[12px] text-[#155B33]">Ce flux vous guide étape par étape pour encoder les données sur une carte RFID.</p>
        </div>

        {{-- ══ STEP 1 CARD ══ --}}
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[12.5px] font-bold tracking-[0.06em] text-[#131313] uppercase">Étape 1 : Sélection de la carte RFID</p>
            <p class="text-[11.5px] text-[#8A857A] mt-0.5 mb-4">Sélectionnez une carte RFID à encoder ou scannez une carte vierge.</p>

            <div class="grid grid-cols-1 lg:grid-cols-[220px_1fr_215px] gap-4 items-stretch">
                {{-- method --}}
                <div class="rounded-xl border border-[#EFEDE6] p-3.5">
                    <p class="text-[10.5px] font-bold tracking-wide text-[#3B382F] uppercase mb-3">Méthode de sélection</p>
                    <div class="space-y-2.5">
                        @foreach([['Scanner une carte vierge','Approchez la carte du lecteur RFID',true],['Entrer UID manuellement',"Saisir l'UID/ID de la carte",false],['Sélectionner un visiteur','Encoder pour un visiteur existant',false]] as [$t,$s,$on])
                        <button data-toast="Méthode : {{ $t }} (démo)" class="w-full flex items-start gap-2.5 rounded-lg border px-3 py-2.5 text-left {{ $on ? 'border-[#CFE8D8] bg-[#EAF6EE]' : 'border-[#EFEDE6] bg-white hover:border-[#D8E5DC]' }}">
                            <span class="mt-0.5 w-[15px] h-[15px] rounded-full border {{ $on ? 'border-[#157A43]' : 'border-[#C9C5BA]' }} flex items-center justify-center shrink-0">@if($on)<span class="w-[7px] h-[7px] rounded-full bg-[#157A43]"></span>@endif</span>
                            <span><span class="block text-[12px] font-semibold text-[#131313]">{{ $t }}</span><span class="block text-[10.5px] text-[#8A857A]">{{ $s }}</span></span>
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- waiting for card --}}
                <div class="rounded-xl border border-[#EFEDE6] p-4 flex flex-col items-center justify-center text-center">
                    <img src="{{ asset('images/siarc/accred-rfid-write.png') }}" alt="" class="h-[210px] w-auto object-contain">
                    <p class="text-[15px] font-bold text-[#131313] mt-2">En attente de carte...</p>
                    <p class="text-[11.5px] text-[#8A857A]">Approchez une carte vierge du lecteur RFID</p>
                    <button onclick="window.location.reload()" class="mt-3 siarc-btn text-[12.5px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3.5 py-2 hover:bg-[#FBFAF6]"><i data-lucide="refresh-cw" class="w-4 h-4 text-[#8A857A]"></i>Actualiser le lecteur</button>
                </div>

                {{-- active reader --}}
                <div class="rounded-xl border border-[#EFEDE6] p-3.5">
                    <p class="text-[10.5px] font-bold tracking-wide text-[#3B382F] uppercase mb-2">Lecteur RFID actif</p>
                    <span class="inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#157A43] mb-2.5"><span class="w-2 h-2 rounded-full bg-[#157A43]"></span>Connecté</span>
                    <dl class="space-y-2 text-[11.5px]">
                        @foreach([['Nom du lecteur','SIARC-Writer-01'],['Modèle','NXP USB Reader'],['Technologie','MIFARE DESFire EV2'],['Interface','USB'],['Statut','Prêt à écrire']] as [$k,$v])
                        <div><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-semibold text-[#131313]">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                    <button data-toast="Lecteur testé : OK (démo)" class="mt-3.5 w-full siarc-btn justify-center text-[12px] font-semibold text-[#157A43] border border-[#CFE8D8] rounded-lg px-3 py-2 hover:bg-[#F4FAF6]"><i data-lucide="scan-line" class="w-3.5 h-3.5"></i>Tester le lecteur</button>
                </div>
            </div>

            {{-- card info --}}
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mt-6 mb-3">Informations de la carte <span class="text-[#B0AB9F] font-semibold">(aucune carte détectée)</span></p>
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 pb-4 border-b border-[#EFEDE6]">
                @foreach(['UID (Hex)','UID (Décimal)','Technologie','Mémoire','Taille','Statut'] as $k)
                <span><span class="block text-[10.5px] text-[#8A857A]">{{ $k }}</span><span class="block text-[13px] font-semibold text-[#3B382F] mt-0.5">--</span></span>
                @endforeach
            </div>
            <div class="flex justify-end pt-4">
                <button data-toast="Étape 2 — Configurer les données (démo : aucune carte détectée)" class="siarc-btn text-[13px] font-semibold text-[#8A857A] border border-[#EFEDE6] rounded-lg px-4 py-2.5 hover:bg-[#FBFAF6]">Suivant : Configurer les données<i data-lucide="arrow-right" class="w-4 h-4"></i></button>
            </div>
        </div>
    </div>

    {{-- ══ RIGHT RAIL ══ --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-4">Aperçu du flux</p>
            <div class="space-y-4">
                @foreach([['scan-line','1. Sélection','Choisissez ou scannez une carte RFID vierge'],['file-cog','2. Données','Configurez les données à encoder sur la carte'],['pen-line','3. Écriture','Écrivez les données sur la carte RFID'],['badge-check','4. Vérification','Vérifiez que les données ont été correctement écrites'],['check-circle-2','5. Terminé',"La carte est prête à l'utilisation"]] as [$ic,$t,$s])
                <div class="flex items-start gap-2.5 {{ $loop->last ? '' : 'relative' }}">
                    @unless($loop->last)<span class="absolute left-[15px] top-8 bottom-[-14px] w-px bg-[#EFEDE6]"></span>@endunless
                    <span class="w-8 h-8 rounded-lg {{ $loop->last ? 'bg-[#E8F5EC]' : 'bg-[#F5F3EE] border border-[#EFEDE6]' }} flex items-center justify-center shrink-0 relative z-10"><i data-lucide="{{ $ic }}" class="w-4 h-4 {{ $loop->last ? 'text-[#157A43]' : 'text-[#3B382F]' }}"></i></span>
                    <span><span class="block text-[12.5px] font-bold text-[#131313]">{{ $t }}</span><span class="block text-[11px] text-[#8A857A] leading-snug">{{ $s }}</span></span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-3">Données typiques encodées</p>
            <div class="space-y-2">
                @foreach(['ID Badge / Numéro unique','Nom complet','Type de badge',"Pavillon / Zone d'accès","Niveau d'accès",'Date de validité','Photo (optionnel)','Données personnalisées'] as $d)
                <p class="flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="check" class="w-3.5 h-3.5 text-[#157A43]"></i>{{ $d }}</p>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-3">Bonnes pratiques</p>
            <div class="space-y-2.5">
                @foreach([['lightbulb',"Assurez-vous que la carte est vierge avant l'encodage."],['clock',"Ne retirez pas la carte pendant l'écriture."],['badge-check',"Vérifiez toujours les données après l'écriture."],['info','Gardez le lecteur propre et à jour.']] as [$ic,$t])
                <p class="flex items-start gap-2 text-[11.5px] text-[#3B382F] leading-snug"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5 text-[#8A857A] shrink-0 mt-0.5"></i>{{ $t }}</p>
                @endforeach
            </div>
        </div>
    </div>
</div>
