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
            case 'deleted': text = 'Data berhasil dihapus'; icon = 'warning'; break;
            case 'error': text = 'Terjadi kesalahan sistem'; icon = 'error'; title = 'Ops!'; break;
            case 'error_saldo': text = 'Saldo tidak mencukupi!'; icon = 'error'; title = 'Gagal'; break;
            case 'promoted': text = 'Santri telah naik kelas'; break;
            case 'graduated': text = 'Santri telah diluluskan'; break;
            case 'paid': text = 'Pembayaran berhasil dikonfirmasi'; break;
            case 'already_paid': text = 'Tagihan ini sudah lunas'; icon = 'info'; break;
        }

        if (text) {
            Toast.fire({ icon: icon, title: text });
        }
    }
</script>
</body>

</html>