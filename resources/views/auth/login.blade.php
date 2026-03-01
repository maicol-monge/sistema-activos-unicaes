<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Iniciar Sesión - Sistema Activos UNICAES</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --rojo-principal: #7e0001;
            /* rgb(126, 0, 1) */
            --rojo-oscuro: #5c0001;
            /* rgb(92, 0, 1) */
            --dorado: #edbd3f;
            /* rgb(237, 189, 63) */
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
            url("{{ asset('images/fondoLogin.png') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-top: 6px solid var(--rojo-principal);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            color: var(--rojo-principal);
            font-weight: 700;
            margin-top: 10px;
            letter-spacing: -0.5px;
        }

        .form-control:focus {
            border-color: var(--dorado);
            box-shadow: 0 0 0 0.25rem rgba(237, 189, 63, 0.25);
        }

        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
            color: var(--rojo-principal);
        }

        .form-control {
            border-left: none;
        }

        .form-control:focus+.input-group-text {
            border-color: var(--dorado);
        }

        .form-check-input:checked {
            background-color: var(--rojo-principal);
            border-color: var(--rojo-principal);
        }

        .btn-login {
            background-color: var(--rojo-principal);
            color: white;
            font-weight: 600;
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: var(--rojo-oscuro);
            color: var(--dorado);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(92, 0, 1, 0.2);
        }

        .password-input {
            border-right: none;
        }

        .password-toggle {
            border-left: none;
            cursor: pointer;
            user-select: none;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="login-header">
            <i class="fa-solid fa-building-columns fa-3x" style="color: var(--dorado);"></i>
            <h2>UNICAES</h2>
            <p class="text-muted mb-0">Sistema de Control de Activos</p>
        </div>

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-left: 4px solid #842029;">
                <i class="fa-solid fa-circle-exclamation me-2"></i> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="mb-3">
                <label class="form-label text-muted fw-bold" style="font-size: 0.9em;">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" name="correo" class="form-control" value="{{ old('correo') }}" placeholder="usuario@unicaes.edu.sv" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-muted fw-bold" style="font-size: 0.9em;">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input id="password" type="password" name="contrasena" class="form-control password-input" placeholder="••••••••" required>
                    <button id="togglePassword" type="button" class="input-group-text password-toggle" aria-label="Mostrar u ocultar contraseña">
                        <i id="togglePasswordIcon" class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-login w-100">
                Ingresar al Sistema <i class="fa-solid fa-arrow-right-to-bracket ms-2"></i>
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('togglePassword');
            const toggleIcon = document.getElementById('togglePasswordIcon');

            if (!passwordInput || !toggleBtn || !toggleIcon) return;

            toggleBtn.addEventListener('click', () => {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleIcon.classList.toggle('fa-eye', !isPassword);
                toggleIcon.classList.toggle('fa-eye-slash', isPassword);
            });
        });
    </script>
</body>

</html>