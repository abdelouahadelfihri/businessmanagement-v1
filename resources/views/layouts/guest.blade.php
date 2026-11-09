<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sign in') · Business Management</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            min-height: 100dvh;
            background: linear-gradient(135deg, #B0C4DE 0%, #e9eff7 55%, #f6f8fb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            border: 0;
            border-radius: 16px;
        }

        .auth-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.25rem;
            color: #1f2d3d;
            text-decoration: none;
        }

        .auth-brand i {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #B0C4DE;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>
    <div class="card auth-card shadow">
        <div class="card-body p-4 p-sm-5">
            <div class="text-center mb-4">
                <span class="auth-brand"><i class="bi bi-building"></i> Business MS</span>
            </div>

            @yield('content')
        </div>
    </div>

    <script>
        // Show / hide password toggles
        document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const input = document.getElementById(btn.dataset.togglePassword);
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        });
    </script>
</body>

</html>
