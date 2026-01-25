function confirmSubmission(event) {
    event.preventDefault(); // Stop default form submission
    const form = event.target; // Get the form element

    Swal.fire({
        title: 'Pengesahan',
        text: 'Adakah anda pasti ingin menghantar borang ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5a2e8a', // Use primary purple color
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hantar!',
        cancelButtonText: 'Batal',
        background: '#f8f9fa',
        customClass: {
            popup: 'animated fadeInDown'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit(); // Manually submit if confirmed
        }
    });

    return false; // Ensure form doesn't auto-submit
}

// Add Reason Based Popups (Upload or Navigate)
document.addEventListener('DOMContentLoaded', function () {
    const reasonRadios = document.querySelectorAll('input[name="reason"]');
    const supportingDocInput = document.getElementById('supporting_document');

    reasonRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            let config = null;

            if (this.value === 'Masalah kesihatan') {
                config = {
                    title: 'Wajib Muat Naik Dokumen',
                    text: 'Anda memilih "Masalah Kesihatan". Sila muat naik surat sokongan (MC / Laporan Doktor) sekarang.',
                    icon: 'warning'
                };
            } else if (this.value === 'Bertukar Universiti/Kolej') {
                config = {
                    title: 'Wajib Muat Naik Surat Tawaran',
                    text: 'Sila muat naik Surat Tawaran kemasukan dari Universiti/Kolej baharu anda.',
                    icon: 'warning'
                };
            } else if (this.value === 'Mendapat pekerjaan') {
                config = {
                    title: 'Wajib Muat Naik Surat Pekerjaan',
                    text: 'Sila muat naik Surat Tawaran Pekerjaan yang telah anda terima.',
                    icon: 'warning'
                };
            }

            if (config) {
                Swal.fire({
                    ...config,
                    input: 'file',
                    inputAttributes: {
                        'accept': 'image/*, application/pdf',
                        'aria-label': 'Upload supporting document'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Muat Naik',
                    confirmButtonColor: '#5a2e8a',
                    cancelButtonText: 'Batal',
                    preConfirm: (file) => {
                        if (!file) {
                            Swal.showValidationMessage('Sila pilih fail untuk dimuat naik');
                        }
                        return file;
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(result.value);
                        supportingDocInput.files = dataTransfer.files;

                        Swal.fire({
                            title: 'Berjaya!',
                            text: 'Dokumen telah dilampirkan.',
                            icon: 'success'
                        });
                    } else {
                        this.checked = false;
                    }
                });
            } else if (this.value === 'Masalah kewangan') {
                Swal.fire({
                    title: 'Bantuan Kewangan & Zakat',
                    html: 'Anda memilih "Masalah Kewangan". Sila layari portal ZAWAF UiTM untuk maklumat bantuan zakat dan kewangan: <br><br><a href="https://zawaf.uitm.edu.my/" target="_blank" class="btn btn-primary" style="background-color: #5a2e8a; border-color: #5a2e8a;">Layari ZAWAF</a>',
                    icon: 'info',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#6c757d'
                });
            }
        });
    });
});
