<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">

    @vite(['resources/css/change-password-modal.css'])
</head>

<body>
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">Change Password</h5>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <p class="notice"><span>Notice: </span>You are using a temporary password. Set a new secure password
                        to continue.</p>

                    <form id="changePasswordForm" action="{{ route('change-password') }}" method="POST">
                        @csrf

                        <div id="errorMsg"></div>

                        <label for="current_password">
                            Temporary Password <span class="text-danger">*</span>
                        </label>
                        <div class="password-wrapper">
                            <input type="password" name="current_password" id="current_password">
                            <img class="toggle-password-icon"
                                src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/icons/eye.svg"
                                alt="Show password">
                        </div>

                        <label for="new_password">New Password <span class="text-danger">*</span></label>
                        <div class="password-wrapper">
                            <input type="password" name="new_password" id="new_password">
                            <img class="toggle-password-icon"
                                src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/icons/eye.svg"
                                alt="Show password">
                        </div>

                        <label for="new_password_confirmation">Confirm New Password <span
                                class="text-danger">*</span></label>
                        <div class="password-wrapper">
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation">
                            <img class="toggle-password-icon"
                                src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/icons/eye.svg"
                                alt="Show password">
                        </div>

                        <div class="action-buttons">
                            <button type="button" class="btn btn-sm secondary-btn" id="notNowBtn">
                                Not Now
                            </button>
                            <button type="submit" class="btn btn-sm primary-btn" id="saveBtn">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"
                                    style="display: none;"></span>
                                <span class="btn-text">Change Password</span>
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('changePasswordModal');
            const modal = new bootstrap.Modal(modalEl);
            const notNowBtn = document.getElementById('notNowBtn');
            const saveBtn = document.getElementById('saveBtn');
            const form = document.getElementById('changePasswordForm');
            const msg = document.getElementById('errorMsg');

            // ONLY SHOW MODAL IF: has temp password + never skipped before
            @auth
            @if (auth()->user()->hasTempPassword() && !session('temp_password_skipped_prompt'))
                modal.show();
            @endif
        @endauth

        // "Not Now" button → tell backend to remember skip
        if (notNowBtn) {
            notNowBtn.addEventListener('click', () => {
                fetch('{{ route('skip-temp-password') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(() => modal.hide())
                    .catch(() => modal.hide()); // hide anyway
            });
        }

        // Submit new password
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            msg.style.display = 'none';
            msg.textContent = '';

            saveBtn.disabled = true;

            const spinner = saveBtn.querySelector('.spinner-border');
            const btnText = saveBtn.querySelector('.btn-text');

            spinner.style.display = 'inline-block';
            btnText.textContent = 'Saving...';

            const formData = new FormData(form);

            try {
                const resp = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': formData.get('_token'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const json = await resp.json();

                if (json.success) {
                    msg.className = 'changedSuccess';
                    msg.innerHTML = 'Password changed successfully! Reloading...';
                    msg.style.display = 'flex';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    msg.className = 'changedError';
                    msg.innerHTML = json.message || 'Something went wrong.';
                    msg.style.display = 'flex';
                }
            } catch (err) {
                msg.className = 'changedError';
                msg.innerHTML = 'Network error. Try again.';
                msg.style.display = 'flex';
            } finally {
                spinner.style.display = 'none';
                btnText.textContent = 'Change Password';
                saveBtn.disabled = false;
            }
        });

        // Toggle password visibility
        document.querySelectorAll('.toggle-password-icon').forEach(icon => {
            icon.addEventListener('click', () => {
                const input = icon.previousElementSibling;
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                icon.src = isPassword ?
                    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/icons/eye-slash.svg' :
                    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/icons/eye.svg';
            });
        });
        });
    </script>
</body>

</html>
