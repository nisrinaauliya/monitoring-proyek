<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('img/logo_wag.png') }}" type="image/png">
    <title>Login - Sistem Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">

    <div class="card border shadow-sm p-4" style="width: 100%; max-width: 400px; border-radius: 16px;">
        
        <div class="text-center mb-4">
            <img src="{{ asset('img/logo_wag.png') }}" alt="Logo" style="height: 80px;" class="mb-3">
            <p class="text-muted mb-0" style="font-size: 12px;">
                <span style="display: flex; align-items: center; gap: 10px;">
                    <span style="flex: 1; height: 1px; background-color: #ddd;"></span>
                    Salam Satu Hati
                    <span style="flex: 1; height: 1px; background-color: #ddd;"></span>
                </span>
            </p>
        </div>

        @if($errors->any())
            <div class="text-danger text-center mb-3" style="font-size: 12px;">
                <i class="bi bi-exclamation-circle me-1"></i>Username atau password salah.
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-white {{ $errors->any() ? 'border-danger' : '' }}">
                        <i class="bi bi-person text-muted"></i>
                    </span>
                    <input type="text" name="username"
                        class="form-control {{ $errors->any() ? 'border-danger' : '' }}"
                        placeholder="Username" value="{{ old('username') }}">
                </div>
            </div>

            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-white {{ $errors->any() ? 'border-danger' : '' }}">
                        <i class="bi bi-lock text-muted"></i>
                    </span>
                    <input type="password" name="password" id="passwordInput"
                        class="form-control {{ $errors->any() ? 'border-danger' : '' }}"
                        placeholder="Password">
                    <button class="btn btn-outline-secondary bg-white {{ $errors->any() ? 'border-danger' : '' }}" type="button" onclick="togglePassword()">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn w-100 text-white fw-semibold mb-3" 
                style="background-color: #af2027; border-color: #af2027; border-radius: 8px; padding: 10px;">
                Login
            </button>

            <i><p class="text-muted text-end mb-0" style="font-size: 12px;">Silahkan hubungi IT untuk informasi login</p></i>
        </form>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
    </script>
</body>
</html>