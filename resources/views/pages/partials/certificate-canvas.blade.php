{{-- Personalised membership-certificate canvas: real vendor data patched over the
     cert-full.png design. Font sizes use cqw (container-query width) so it scales
     correctly whether shown full-width or inside a narrow column.
     Expects: $ccName, $ccNumber, $ccStart, $ccEnd. Optional: $ccCode, $ccQrUrl, $ccQrId. --}}
@php $ccQrId = $ccQrId ?? 'cert-canvas-qr'; @endphp
<div style="container-type: inline-size; position: relative; width: 100%; aspect-ratio: 1536 / 1024;" class="rounded-lg overflow-hidden shadow-md self-start">
    <img src="{{ asset('images/landing/cert-full.png') }}" alt="{{ $ccName ?? '' }}" style="position:absolute; inset:0; width:100%; height:100%;">

    @isset($ccQrUrl)
    <div style="position:absolute; left:84.55%; top:27.15%; width:9.9%; height:14.95%; display:flex; align-items:center; justify-content:center; background:#fff; border-radius:4px;">
        <div id="{{ $ccQrId }}"></div>
    </div>
    @endisset

    {{-- Name --}}
    <div style="position:absolute; left:24%; top:33.9%; width:34%; height:5.4%; background:#FCFBF6; display:flex; align-items:flex-end; justify-content:center;">
        <span style="font-family:'Poppins',system-ui,sans-serif; font-weight:700; color:#1B1B18; line-height:1; white-space:nowrap; font-size:2.55cqw;">{{ $ccName }}</span>
    </div>
    {{-- Certificate number --}}
    <div style="position:absolute; left:13.7%; top:56.6%; width:13.5%; height:2.6%; background:#FDFCF7; display:flex; align-items:center;">
        <span style="font-family:'Poppins',system-ui,sans-serif; font-weight:600; color:#1B1B18; white-space:nowrap; font-size:1.05cqw;">{{ $ccNumber }}</span>
    </div>
    @isset($ccCode)
    <div style="position:absolute; left:12.9%; top:65.2%; width:12%; height:2.2%; background:#FCFBF6; display:flex; align-items:center;">
        <span style="font-family:'Poppins',system-ui,sans-serif; font-weight:500; color:#3B382F; white-space:nowrap; font-size:0.9cqw;">{{ $ccCode }}</span>
    </div>
    @endisset
    {{-- Issue date --}}
    <div style="position:absolute; left:14%; top:73.9%; width:9%; height:2.1%; background:#FCFBF6; display:flex; align-items:center;">
        <span style="font-family:'Poppins',system-ui,sans-serif; font-weight:600; color:#1B1B18; white-space:nowrap; font-size:0.8cqw;">{{ $ccStart }}</span>
    </div>
    {{-- Expiry date --}}
    <div style="position:absolute; left:24.3%; top:73.9%; width:9%; height:2.1%; background:#FCFBF6; display:flex; align-items:center;">
        <span style="font-family:'Poppins',system-ui,sans-serif; font-weight:600; color:#1B1B18; white-space:nowrap; font-size:0.8cqw;">{{ $ccEnd }}</span>
    </div>
</div>
@isset($ccQrUrl)
<script>
    (function () {
        var t = document.getElementById(@json($ccQrId));
        if (t && window.QRCode) { new QRCode(t, { text: @json($ccQrUrl), width: 72, height: 72, correctLevel: QRCode.CorrectLevel.M }); }
    })();
</script>
@endisset
