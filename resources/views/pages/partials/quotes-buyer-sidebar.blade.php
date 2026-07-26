{{-- Quote-flow sidebar.

     Delegates to the single canonical dashboard sidebar so quote pages show
     exactly the same navigation as every other dashboard page. That partial is
     role-scoped, so a seller building a proposal sees the seller nav and a buyer
     reviewing one sees the buyer nav — there is no separate item list to drift,
     and no hardcoded badge counts.

     Keeps the `qb-sidebar` id that quotes-buyer-header.blade.php toggles. --}}
@include('pages.partials.dashboard-sidebar', ['sidebarId' => 'qb-sidebar'])
