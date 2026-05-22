</main>
</div>
</div>

<script>
    // Global Toast Configuration
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            popup: 'swal2-emerald-popup'
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // Helper to call Alerts from JS
    function showAlert(title, text, icon = 'success') {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            customClass: {
                confirmButton: 'swal2-emerald-confirm',
                popup: 'swal2-emerald-popup'
            }
        });
    }

    // Handle URL Messages (msg=...)
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const status = urlParams.get('status');

    if (msg) {
        let title = 'Berhasil!';
        let icon = 'success';
        let text = '';

        switch (msg) {
            case 'updated': text = 'Data berhasil diperbarui'; break;
            case 'added': text = 'Data berhasil disimpan'; break;
            case 'deleted': text = 'Data berhasil dihapus'; icon = 'success'; break;
            case 'error': text = 'Terjadi kesalahan sistem'; icon = 'error'; title = 'Ops!'; break;
            case 'error_saldo': text = 'Saldo tidak mencukupi!'; icon = 'error'; title = 'Gagal'; break;
            case 'error_self': text = 'Tidak bisa menghapus akun sendiri!'; icon = 'error'; title = 'Gagal'; break;
            case 'promoted': text = 'Santri telah naik kelas'; break;
            case 'graduated': text = 'Santri telah diluluskan'; break;
            case 'paid': text = 'Pembayaran berhasil dikonfirmasi'; break;
            case 'already_paid': text = 'Tagihan ini sudah lunas'; icon = 'info'; break;
        }

        if (text) {
            Toast.fire({ icon: icon, title: text });
        }
    }

    // Initialize DataTables
    $(document).ready(function() {
        if ($('#dataTable').length > 0) {
            $('#dataTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
                },
                "pageLength": 10,
                "responsive": true
            });
        }
    });

    // Mobile sidebar toggle
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const appSidebar = document.getElementById('appSidebar');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const sidebarCloseButton = document.getElementById('sidebarCloseButton');
    const mobileMediaQuery = window.matchMedia('(max-width: 767px)');

    function toggleSidebar(forceOpen = null) {
        if (!appSidebar || !mobileOverlay) return;
        const shouldOpen = forceOpen === null ? !appSidebar.classList.contains('open') : forceOpen;
        appSidebar.classList.toggle('open', shouldOpen);
        mobileOverlay.classList.toggle('open', shouldOpen);
        document.body.classList.toggle('overflow-hidden', shouldOpen);
        appSidebar.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');
        if (mobileMenuButton) {
            mobileMenuButton.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        }
    }

    function syncSidebarState() {
        if (!mobileMediaQuery.matches) {
            appSidebar?.classList.remove('open');
            mobileOverlay?.classList.remove('open');
            document.body.classList.remove('overflow-hidden');
            appSidebar?.setAttribute('aria-hidden', 'false');
            if (mobileMenuButton) {
                mobileMenuButton.setAttribute('aria-expanded', 'false');
            }
            return;
        }

        appSidebar?.setAttribute('aria-hidden', appSidebar.classList.contains('open') ? 'false' : 'true');
    }

    if (mobileMenuButton) {
        mobileMenuButton.setAttribute('aria-expanded', 'false');
        mobileMenuButton.addEventListener('click', () => toggleSidebar());
    }

    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', () => toggleSidebar(false));
    }

    if (sidebarCloseButton) {
        sidebarCloseButton.addEventListener('click', () => toggleSidebar(false));
    }

    document.querySelectorAll('#appSidebar a').forEach((link) => {
        link.addEventListener('click', () => toggleSidebar(false));
    });

    if (mobileMediaQuery.addEventListener) {
        mobileMediaQuery.addEventListener('change', syncSidebarState);
    } else if (mobileMediaQuery.addListener) {
        mobileMediaQuery.addListener(syncSidebarState);
    }

    syncSidebarState();
</script>
</body>

</html>
