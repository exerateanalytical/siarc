{{-- ============================================================
     THE empty state.

     An empty table is not the same thing as a broken page, but it reads as
     one — which is most of why this console was reported as "90% dead". Every
     section that can render nothing renders this instead, and it has to answer
     three questions: what is this section for, why is it empty, and what would
     fill it.

     It also has to keep two claims apart, because they look identical on a
     screenshot and are completely different statements about the platform:

       state="empty"    nothing has happened yet. The feature is connected and
                        working; the register genuinely holds no rows. A zero
                        here is a measurement.
       state="unwired"  there is no data source behind this section. Nothing
                        the operator does will fill it. An absence is not a
                        zero, and this variant says so in words rather than
                        showing a confident 0.

     Usage:
       @include('pages.partials.empty-state', [
           'icon'   => 'inbox',                    lucide name, optional
           'title'  => 'Aucun avis en attente',
           'body'   => 'Les avis déposés par les acheteurs arrivent ici …',
           'state'  => 'empty',                    or 'unwired'
           'note'   => 'Non connecté',             unwired only, optional
           'action' => ['label' => '…', 'href' => '…'],   optional
       ])

     Styling comes entirely from pages/partials/ui-kit.blade.php — no bespoke
     colours or sizes here, so dark mode and UiConsistencyTest both hold.
     ============================================================ --}}
@php
    $esState  = ($state ?? 'empty') === 'unwired' ? 'unwired' : 'empty';
    $esIcon   = $icon ?? ($esState === 'unwired' ? 'plug-zap' : 'inbox');
    $esAction = $action ?? null;
@endphp
<div class="ui-empty-state @if($esState === 'unwired') ui-empty-state--unwired @endif">
    <div class="ui-empty-icon">
        <i data-lucide="{{ $esIcon }}" class="w-[18px] h-[18px]"></i>
    </div>

    <p class="ui-empty-title">{{ $title }}</p>
    <p class="ui-empty-body">{{ $body }}</p>

    @if($esState === 'unwired' && ! empty($note))
        <span class="ui-empty-note">{{ $note }}</span>
    @endif

    @if($esAction && ! empty($esAction['href']))
        <div class="ui-empty-actions">
            <a href="{{ $esAction['href'] }}" class="ui-btn ui-btn-secondary ui-btn-sm">
                {{ $esAction['label'] }}
            </a>
        </div>
    @endif
</div>
