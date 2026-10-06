@push('styles')
<style>
    @media print {
        .sidebar, .btn-toolbar, .no-print { display: none !important; }
        .main-content { margin: 0 !important; width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; }
        body { background: #fff; }
        .card { box-shadow: none; border: 0; }
    }
</style>
@endpush
