</div> <!-- end container content -->
<footer class="footer mt-5 pb-4">
    <div class="container text-center">
        <?php if (($_SESSION['role'] ?? '') != 'admin'): ?>
            <div class="mb-3">
                <button class="btn btn-outline-purple btn-sm rounded-pill px-4" id="contactUsBtn"
                    style="color: #6f42c1; border-color: #6f42c1;">
                    <i class="fas fa-headset me-2"></i>Contact Us
                </button>
            </div>
        <?php endif; ?>
        <span class="text-muted small">©
            <?php echo date('Y'); ?> UiTM Student Withdrawal System
        </span>
    </div>
</footer>

<style>
    .btn-outline-purple:hover {
        background-color: #6f42c1 !important;
        color: white !important;
    }
</style>

<script>
    document.getElementById('contactUsBtn').addEventListener('click', function () {
        Swal.fire({
            title: '<h4 class="fw-bold mb-0">Contact Support</h4>',
            html: `
                <div class="text-start p-2">
                    <p class="text-muted mb-4 text-center">Have any inquiries? Our support team is here to help you.</p>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light p-3 rounded-circle me-3">
                            <i class="fas fa-envelope" style="color: #6f42c1;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Email Support</h6>
                            <p class="mb-0 text-muted small">withdrawalapplication@uitm.edu.my</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light p-3 rounded-circle me-3">
                            <i class="fas fa-phone-alt text-success"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Phone / WhatsApp</h6>
                            <p class="mb-0 text-muted small">+603-1442 5658</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-light p-3 rounded-circle me-3">
                            <i class="fas fa-clock text-warning"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Office Hours</h6>
                            <p class="mb-0 text-muted small">Monday - Friday: 8:00 AM - 5:00 PM</p>
                        </div>
                    </div>
                </div>
            `,
            showConfirmButton: true,
            confirmButtonText: 'Close',
            confirmButtonColor: '#6f42c1',
            customClass: {
                confirmButton: 'rounded-pill px-4'
            },
            showClass: {
                popup: 'animate__animated animate__fadeInUp'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutDown'
            }
        });
    });
</script>
<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="assets/js/script.js"></script>
</body>

</html>