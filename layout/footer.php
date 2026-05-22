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

    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const mobileMenuPanel = document.getElementById('mobileMenuPanel');
    const mobileMenuBackdrop = document.getElementById('mobileMenuBackdrop');
    const mobileMenuCloseButton = document.getElementById('mobileMenuCloseButton');
    const mobileInstallButton = document.getElementById('mobileInstallButton');
    const installBanner = document.getElementById('installBanner');
    const installBannerButton = document.getElementById('installBannerButton');
    const installBannerDismiss = document.getElementById('installBannerDismiss');
    const mobileMediaQuery = window.matchMedia('(max-width: 767px)');
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
    let deferredInstallPrompt = null;

    function toggleMobileMenu(forceOpen = null) {
        if (!mobileMenuPanel) return;
        const shouldOpen = forceOpen === null ? !mobileMenuPanel.classList.contains('open') : forceOpen;
        mobileMenuPanel.classList.toggle('open', shouldOpen);
        document.body.classList.toggle('overflow-hidden', shouldOpen);
        document.body.classList.toggle('sidebar-open', shouldOpen);
        mobileMenuPanel.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');
        if (mobileMenuButton) {
            mobileMenuButton.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        }
    }

    function syncMobileMenuState() {
        if (!mobileMediaQuery.matches) {
            mobileMenuPanel?.classList.remove('open');
            document.body.classList.remove('overflow-hidden');
            document.body.classList.remove('sidebar-open');
            mobileMenuPanel?.setAttribute('aria-hidden', 'true');
            if (mobileMenuButton) {
                mobileMenuButton.setAttribute('aria-expanded', 'false');
            }
            return;
        }

        mobileMenuPanel?.setAttribute('aria-hidden', mobileMenuPanel.classList.contains('open') ? 'false' : 'true');
    }

    if (mobileMenuButton) {
        mobileMenuButton.setAttribute('aria-expanded', 'false');
        mobileMenuButton.addEventListener('click', () => toggleMobileMenu());
    }

    if (mobileMenuBackdrop) {
        mobileMenuBackdrop.addEventListener('click', () => toggleMobileMenu(false));
    }

    if (mobileMenuPanel) {
        mobileMenuPanel.addEventListener('click', (event) => {
            const surface = event.target.closest('.mobile-menu-surface');
            if (!surface) {
                toggleMobileMenu(false);
                return;
            }
            event.stopPropagation();
        });
    }

    if (mobileMenuCloseButton) {
        mobileMenuCloseButton.addEventListener('click', () => toggleMobileMenu(false));
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mobileMenuPanel?.classList.contains('open')) {
            toggleMobileMenu(false);
        }
    });

    document.querySelectorAll('#mobileMenuPanel a').forEach((link) => {
        link.addEventListener('click', () => toggleMobileMenu(false));
    });

    if (mobileMediaQuery.addEventListener) {
        mobileMediaQuery.addEventListener('change', syncMobileMenuState);
    } else if (mobileMediaQuery.addListener) {
        mobileMediaQuery.addListener(syncMobileMenuState);
    }

    syncMobileMenuState();

    function showInstallEntryPoints() {
        if (isStandalone) {
            return;
        }

        if (mobileInstallButton) {
            mobileInstallButton.hidden = false;
            mobileInstallButton.classList.remove('hidden');
            mobileInstallButton.classList.add('inline-flex');
        }

        if (installBanner && deferredInstallPrompt) {
            installBanner.hidden = false;
            requestAnimationFrame(() => installBanner.classList.add('show'));
        }
    }

    function hideInstallEntryPoints() {
        installBanner?.classList.remove('show');
        if (installBanner) {
            installBanner.hidden = true;
        }

        if (mobileInstallButton) {
            mobileInstallButton.hidden = true;
            mobileInstallButton.classList.add('hidden');
            mobileInstallButton.classList.remove('inline-flex');
        }
    }

    async function promptInstallApp() {
        if (isStandalone) {
            return;
        }

        if (deferredInstallPrompt) {
            deferredInstallPrompt.prompt();
            const choice = await deferredInstallPrompt.userChoice;
            if (choice.outcome !== 'accepted') {
                showAlert('Install aplikasi', 'Anda masih bisa install kapan saja dari tombol menu browser.', 'info');
            }
            deferredInstallPrompt = null;
            installBanner?.classList.remove('show');
            return;
        }

        if (isIos) {
            showAlert('Install aplikasi', 'Di iPhone/iPad, buka menu Share lalu pilih Add to Home Screen.', 'info');
            return;
        }

        showAlert('Install aplikasi', 'Jika tombol install belum muncul, buka menu browser lalu pilih Install app atau Tambahkan ke layar utama.', 'info');
    }

    mobileInstallButton?.addEventListener('click', promptInstallApp);
    installBannerButton?.addEventListener('click', promptInstallApp);
    installBannerDismiss?.addEventListener('click', () => {
        installBanner?.classList.remove('show');
    });

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        showInstallEntryPoints();
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        hideInstallEntryPoints();
        showAlert('Berhasil', 'Aplikasi berhasil di-install di perangkat ini.', 'success');
    });

    showInstallEntryPoints();

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('<?= base_url('sw.js?v=2') ?>').catch(() => {
                // PWA registration failure should not block the app.
            });
        });
    }
</script>
</body>

</html>
