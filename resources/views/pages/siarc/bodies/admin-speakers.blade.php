@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // ── KPI figures (approved design metrics) ──────────────────────────────────
    $kpis = [
        ['users-round','#157A43','#E2F3E8','Total intervenants','126','+18.4%','vs SIARC 2024','up'],
        ['mic','#7C4FE0','#F0EAFB','Conférenciers','68','+12.7%','vs SIARC 2024','up'],
        ['presentation','#C97A16','#FDF3E0','Panélistes','42','+25.0%','vs SIARC 2024','up'],
        ['users','#3565DE','#E8EFFB','Animateurs','16','-5.9%','vs SIARC 2024','down'],
    ];

    // ── Speakers (real data if available, else approved design roster) ─────────
    $speakers = collect();
    try {
        if (Schema::hasTable('speakers') && function_exists('siarcEvent')) {
            $speakers = DB::table('speakers')
                ->where('event_id', siarcEvent()?->id ?? 0)
                ->orderBy('sort_order')
                ->get(['id','name','role_fr','organization','photo','is_featured']);
        }
    } catch (\Throwable $e) { $speakers = collect(); }

    // Roster shown in the design (approved figures) — used when DB is empty.
    $roster = [
        ['id'=>1,'name'=>'Dr. Alain Mbarga','sub'=>'Expert en Innovation & Transformation Digitale','role'=>'Conférencier','cat'=>'Innovation & Technologie','flag'=>'🇨🇲','country'=>'Cameroun','sessions'=>2,'status'=>'Confirmé'],
        ['id'=>2,'name'=>'Marie Claire Nguimatsia','sub'=>'Designer & Consultante','role'=>'Panéliste','cat'=>'Design & Création','flag'=>'🇨🇲','country'=>'Cameroun','sessions'=>1,'status'=>'Confirmé'],
        ['id'=>3,'name'=>'Paul Tchameni','sub'=>'CEO, TechCraft Africa','role'=>'Panéliste','cat'=>'Entrepreneuriat & Financement','flag'=>'🇨🇲','country'=>'Cameroun','sessions'=>1,'status'=>'Confirmé'],
        ['id'=>4,'name'=>'Fatou Diop','sub'=>'Fondatrice, Digital Artisans Hub','role'=>'Conférencière','cat'=>'Innovation & Technologie','flag'=>'🇸🇳','country'=>'Sénégal','sessions'=>2,'status'=>'Confirmé'],
        ['id'=>5,'name'=>'Jean-Baptiste Makosso','sub'=>'Économiste & Consultant','role'=>'Conférencier','cat'=>'Économie & Marchés','flag'=>'🇨🇬','country'=>'Congo','sessions'=>1,'status'=>'Invité'],
        ['id'=>6,'name'=>'Amina Hassan','sub'=>'Directrice, Heritage Africa','role'=>'Panéliste','cat'=>'Culture & Patrimoine','flag'=>'🇰🇪','country'=>'Kenya','sessions'=>1,'status'=>'En attente'],
        ['id'=>7,'name'=>'Kwame Mensah','sub'=>'CEO, African Craft Export','role'=>'Conférencier','cat'=>'Commerce & Export','flag'=>'🇬🇭','country'=>'Ghana','sessions'=>1,'status'=>'Confirmé'],
        ['id'=>8,'name'=>'Sophie Leclerc','sub'=>'Experte en Design Durable','role'=>'Animatrice','cat'=>'Développement Durable','flag'=>'🇫🇷','country'=>'France','sessions'=>1,'status'=>'Confirmé'],
        ['id'=>9,'name'=>'Carlos Mendes','sub'=>'Spécialiste Marketing Digital','role'=>'Conférencier','cat'=>'Marketing & Digital','flag'=>'🇵🇹','country'=>'Portugal','sessions'=>1,'status'=>'En attente'],
        ['id'=>10,'name'=>'Nadine Bella','sub'=>'Architecte & Urbaniste','role'=>'Panéliste','cat'=>'Architecture & Artisanat','flag'=>'🇨🇲','country'=>'Cameroun','sessions'=>1,'status'=>'Confirmé'],
    ];

    // Map DB rows into the same shape when present.
    $rows = $speakers->isNotEmpty()
        ? $speakers->map(fn($s)=>[
            'id'=>$s->id,'name'=>$s->name,'sub'=>$s->role_fr,'role'=>$s->role_fr ?: 'Intervenant',
            'cat'=>$s->organization,'flag'=>'🇨🇲','country'=>'Cameroun','sessions'=>1,
            'status'=>$s->is_featured ? 'Confirmé' : 'En attente',
          ])->toArray()
        : $roster;

    // Status → badge tone
    $tone = function($s){
        return match(true){
            in_array($s,['Confirmé','Confirmée']) => ['#E2F3E8','#157A43'],
            $s==='Invité' => ['#E8EFFB','#3565DE'],
            $s==='En attente' => ['#FDF3E0','#C97A16'],
            default => ['#F1F1EF','#8A857A'],
        };
    };
    // Role → badge tone (subtle pills, as in design)
    $roleTone = function($r){
        return match(true){
            str_starts_with($r,'Conférenci') => ['#F0EAFB','#7C4FE0'],
            str_starts_with($r,'Panél')      => ['#FDF3E0','#C97A16'],
            str_starts_with($r,'Anim')       => ['#FDE8E8','#C0010C'],
            default => ['#E8EFFB','#3565DE'],
        };
    };

    $tabs = [['Tous',126,true],['Confirmés',98,false],['En attente',18,false],['Invités',10,false]];
    $sel = $rows[0]; // featured detail panel = first speaker
@endphp

{{-- ══ PAGE HEADER ACTIONS (KPI row + primary buttons) ══ --}}
<div class="grid xl:grid-cols-4 gap-5">

    {{-- LEFT : main column (KPIs + toolbar + table) ─────────────────────────── --}}
    <div class="xl:col-span-3 space-y-5">

        {{-- KPI CARDS --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($kpis as [$icon,$color,$tile,$label,$val,$trend,$vs,$dir])
            <div class="siarc-card siarc-shadow p-4">
                <div class="flex items-center gap-2.5">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i></span>
                    <p class="text-[12px] text-[#55524A] font-semibold leading-tight">{{ $label }}</p>
                </div>
                <p class="mt-3 text-[26px] font-extrabold text-[#161513] leading-none tracking-tight">{{ $val }}</p>
                <p class="flex items-center gap-1 mt-2 text-[11px] font-semibold {{ $dir==='up' ? 'text-siarc-green' : 'text-siarc-red' }}">
                    <i data-lucide="{{ $dir==='up' ? 'arrow-up' : 'arrow-up' }}" class="w-3 h-3 {{ $dir==='down' ? 'rotate-180' : '' }}"></i>{{ $trend }}
                    <span class="text-[#B0AB9F] font-normal">{{ $vs }}</span>
                </p>
            </div>
            @endforeach
        </div>

        {{-- FILTER TOOLBAR --}}
        <div class="siarc-card siarc-shadow p-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-[#B0AB9F]"></i>
                        <input type="text" placeholder="Rechercher un intervenant..." class="w-full text-[12.5px] rounded-xl border border-[#ECEAE3] bg-[#FBFAF7] pl-9 pr-3 py-2.5 focus:outline-none focus:border-[#D8E5DC]">
                    </div>
                </div>
                @foreach([['Rôle','Tous les rôles'],['Catégorie','Toutes les catégories'],['Pays','Tous les pays'],['Statut','Tous les statuts']] as [$lbl,$ph])
                <div class="min-w-[150px]">
                    <p class="text-[11px] font-semibold text-[#8A857A] mb-1">{{ $lbl }}</p>
                    <button class="w-full flex items-center justify-between gap-2 text-[12.5px] text-[#3B382F] rounded-xl border border-[#ECEAE3] bg-white px-3 py-2.5 hover:border-[#D8E5DC]">
                        {{ $ph }} <i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i>
                    </button>
                </div>
                @endforeach
                <button class="text-[12.5px] font-semibold text-siarc-green rounded-xl border border-[#D8E5DC] px-4 py-2.5 hover:bg-[#E2F3E8]">Réinitialiser</button>
            </div>
        </div>

        {{-- TABS + SORT + VIEW TOGGLE --}}
        <div class="siarc-card siarc-shadow overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 pt-4 border-b border-[#F1F1EF]">
                <div class="flex items-center gap-6">
                    @foreach($tabs as [$t,$n,$active])
                    <button class="pb-3 text-[13px] font-semibold border-b-2 {{ $active ? 'text-siarc-green border-siarc-green' : 'text-[#8A857A] border-transparent hover:text-[#3B382F]' }}">
                        {{ $t }} <span class="{{ $active ? 'text-siarc-green' : 'text-[#B0AB9F]' }}">({{ $n }})</span>
                    </button>
                    @endforeach
                </div>
                <div class="flex items-center gap-3 pb-3">
                    <span class="text-[11.5px] text-[#8A857A]">Trier par :</span>
                    <button class="flex items-center gap-2 text-[12px] font-medium text-[#3B382F] rounded-lg border border-[#ECEAE3] px-3 py-1.5 hover:border-[#D8E5DC]">Nom (A-Z) <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#B0AB9F]"></i></button>
                    <div class="flex items-center rounded-lg border border-[#ECEAE3] overflow-hidden">
                        <button class="p-1.5 bg-[#E2F3E8] text-siarc-green"><i data-lucide="grid-3x3" class="w-4 h-4"></i></button>
                        <button class="p-1.5 text-[#B0AB9F] hover:bg-[#F7F6F1]"><i data-lucide="clipboard-list" class="w-4 h-4"></i></button>
                    </div>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-left">
                    <thead>
                        <tr class="text-[10.5px] font-semibold uppercase tracking-wide text-[#A8A498] bg-[#FBFAF7]">
                            <th class="py-3 pl-5 pr-3">Intervenant</th>
                            <th class="py-3 px-3">Rôle</th>
                            <th class="py-3 px-3">Catégorie</th>
                            <th class="py-3 px-3">Pays</th>
                            <th class="py-3 px-3">Sessions</th>
                            <th class="py-3 px-3">Statut</th>
                            <th class="py-3 px-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F1EF]">
                        @foreach($rows as $r)
                        @php [$stBg,$stFg] = $tone($r['status']); [$rlBg,$rlFg] = $roleTone($r['role']);
                             $initials = collect(explode(' ', preg_replace('/^(Dr\.|M\.|Mme)\s*/','',$r['name'])))->filter()->take(2)->map(fn($p)=>mb_substr($p,0,1))->implode(''); @endphp
                        <tr class="hover:bg-[#FBFAF7] transition-colors">
                            <td class="py-3 pl-5 pr-3">
                                <a href="{{ $h('siarc.admin.speaker',['id'=>$r['id']]) }}" class="flex items-center gap-3 group">
                                    <span class="w-9 h-9 rounded-full bg-siarc-green text-white text-[12px] font-bold flex items-center justify-center shrink-0">{{ $initials }}</span>
                                    <span class="min-w-0">
                                        <span class="block text-[13px] font-semibold text-[#1A1712] leading-tight group-hover:text-siarc-green">{{ $r['name'] }}</span>
                                        <span class="block text-[11px] text-[#8A857A] truncate max-w-[220px]">{{ $r['sub'] }}</span>
                                    </span>
                                </a>
                            </td>
                            <td class="py-3 px-3"><span class="inline-block text-[11px] font-semibold rounded-md px-2 py-0.5" style="background:{{ $rlBg }};color:{{ $rlFg }}">{{ $r['role'] }}</span></td>
                            <td class="py-3 px-3 text-[12px] text-[#3B382F]">{{ $r['cat'] }}</td>
                            <td class="py-3 px-3 text-[12px] text-[#3B382F]"><span class="mr-1.5">{{ $r['flag'] }}</span>{{ $r['country'] }}</td>
                            <td class="py-3 px-3 text-[12px] text-[#3B382F] font-medium">{{ $r['sessions'] }}</td>
                            <td class="py-3 px-3"><span class="inline-block text-[11px] font-semibold rounded-md px-2 py-0.5" style="background:{{ $stBg }};color:{{ $stFg }}">{{ $r['status'] }}</span></td>
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-1.5 text-[#B0AB9F]">
                                    <a href="{{ $h('siarc.admin.speaker',['id'=>$r['id']]) }}" class="p-1.5 rounded-md hover:bg-[#E2F3E8] hover:text-siarc-green"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                    <a href="{{ $h('siarc.admin.speaker',['id'=>$r['id']]) }}" class="p-1.5 rounded-md hover:bg-[#FDF3E0] hover:text-siarc-ochre"><i data-lucide="settings" class="w-4 h-4"></i></a>
                                    <button class="p-1.5 rounded-md hover:bg-[#F1F1EF]"><i data-lucide="circle-dot" class="w-4 h-4"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-t border-[#F1F1EF]">
                <p class="text-[11.5px] text-[#8A857A]">Affichage de 1 à 10 sur 126 intervenants</p>
                <div class="flex items-center gap-1">
                    <button class="w-8 h-8 rounded-lg border border-[#ECEAE3] flex items-center justify-center text-[#B0AB9F] hover:border-[#D8E5DC]"><i data-lucide="chevron-right" class="w-4 h-4 rotate-180"></i></button>
                    @foreach(['1','2','3','4','5','…','10'] as $i=>$p)
                    <button class="w-8 h-8 rounded-lg text-[12px] font-medium flex items-center justify-center {{ $p==='1' ? 'bg-siarc-green text-white' : 'border border-[#ECEAE3] text-[#3B382F] hover:border-[#D8E5DC]' }}">{{ $p }}</button>
                    @endforeach
                    <button class="w-8 h-8 rounded-lg border border-[#ECEAE3] flex items-center justify-center text-[#3B382F] hover:border-[#D8E5DC]"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                </div>
                <button class="flex items-center gap-2 text-[12px] font-medium text-[#3B382F] rounded-lg border border-[#ECEAE3] px-3 py-1.5 hover:border-[#D8E5DC]">10 / page <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#B0AB9F]"></i></button>
            </div>
        </div>
    </div>

    {{-- RIGHT : action buttons + detail panel ───────────────────────────────── --}}
    <div class="xl:col-span-1 space-y-5">

        {{-- ACTION BUTTONS --}}
        <div class="flex flex-col gap-2.5">
            <button class="siarc-btn siarc-btn-green justify-center px-4 py-2.5 text-[13px]"><i data-lucide="plus" class="w-4 h-4"></i>Ajouter un intervenant</button>
            <button class="siarc-btn siarc-btn-outline justify-center px-4 py-2.5 text-[13px] !text-[#3B382F] !border-[#ECEAE3] bg-white"><i data-lucide="download" class="w-4 h-4"></i>Exporter la liste</button>
        </div>

        {{-- PAYS REPRÉSENTÉS --}}
        <div class="siarc-card siarc-shadow p-4 flex items-center gap-3">
            <span class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#FDE8E8"><i data-lucide="map-pin" class="w-5 h-5" style="color:#C0010C"></i></span>
            <div>
                <p class="text-[12px] text-[#8A857A] font-medium">Pays représentés</p>
                <p class="text-[24px] font-extrabold text-[#161513] leading-none">24</p>
            </div>
        </div>

        {{-- DÉTAILS DE L'INTERVENANT --}}
        @php [$dStBg,$dStFg] = $tone($sel['status']); [$dRlBg,$dRlFg] = $roleTone($sel['role']);
             $dInit = collect(explode(' ', preg_replace('/^(Dr\.|M\.|Mme)\s*/','',$sel['name'])))->filter()->take(2)->map(fn($p)=>mb_substr($p,0,1))->implode(''); @endphp
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">Détails de l'intervenant</h3>

            <div class="flex items-start gap-3">
                <span class="w-16 h-16 rounded-2xl bg-siarc-green text-white text-[20px] font-bold flex items-center justify-center shrink-0 font-display">{{ $dInit }}</span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-[15px] font-bold text-[#1A1712] leading-tight">{{ $sel['name'] }}</p>
                        <span class="text-[10px] font-semibold rounded-md px-2 py-0.5" style="background:{{ $dStBg }};color:{{ $dStFg }}">{{ $sel['status'] }}</span>
                    </div>
                    <span class="inline-block mt-1.5 text-[11px] font-semibold rounded-md px-2 py-0.5" style="background:{{ $dRlBg }};color:{{ $dRlFg }}">{{ $sel['role'] }}</span>
                    <p class="text-[11.5px] text-[#55524A] mt-2 leading-snug">{{ $sel['sub'] }}</p>
                    <p class="text-[11.5px] text-[#8A857A] mt-0.5">{{ $sel['country'] }} <span>{{ $sel['flag'] }}</span></p>
                </div>
            </div>

            {{-- contact --}}
            <ul class="mt-4 space-y-2.5 text-[12px]">
                @foreach(['mail','phone','globe','external-link'] as $ic)
                <li class="flex items-center gap-2.5 text-[#8A857A]"><i data-lucide="{{ $ic }}" class="w-4 h-4 text-[#B0AB9F] shrink-0"></i><span class="truncate">Non renseigné</span></li>
                @endforeach
            </ul>

            {{-- informations --}}
            <div class="mt-5 pt-4 border-t border-[#F1F1EF]">
                <h4 class="text-[12.5px] font-bold text-[#1A1712] mb-3">Informations</h4>
                <dl class="space-y-2 text-[12px]">
                    @foreach([['Catégorie',$sel['cat'] ?: 'Non renseigné'],['Organisation',$sel['cat'] ?: 'Non renseigné'],['Fonction',$sel['role'] ?: 'Non renseigné'],['Pays',$sel['country'] ?: 'Non renseigné'],['Langues','Non renseigné']] as [$k,$v])
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-[#8A857A]">{{ $k }}</dt>
                        <dd class="text-[#1A1712] font-medium text-right">{{ $v }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>

            {{-- sessions assignées --}}
            <div class="mt-5 pt-4 border-t border-[#F1F1EF]">
                <h4 class="text-[12.5px] font-bold text-[#1A1712] mb-3">Sessions assignées</h4>
                <div class="rounded-xl border border-dashed border-[#ECEAE3] p-4 text-center">
                    <p class="text-[11.5px] text-[#8A857A]">À venir</p>
                </div>
            </div>

            {{-- public profile link --}}
            <a href="{{ $h('siarc.public.speakers') }}" class="mt-5 flex items-center justify-center gap-2 text-[12.5px] font-semibold text-siarc-green rounded-xl border border-[#D8E5DC] px-4 py-2.5 hover:bg-[#E2F3E8]">
                Voir le profil public <i data-lucide="external-link" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</div>
