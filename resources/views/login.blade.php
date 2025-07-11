<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | Học Tiếng Lào Admin Panel</title>

    <link rel="icon" type="image/png" href="/assets/imgs/laos.png">
    <link rel="stylesheet" href="/assets/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .card {
            border-radius: 18px;
            box-shadow: 0 4px 24px 0 rgba(35,41,70,0.18);
            background: #232946;
            border: none;
            transition: box-shadow 0.3s;
        }
        .card:hover {
            box-shadow: 0 8px 32px 0 #a992f755;
        }
        .card-header {
            background: none;
            color: #a992f7;
            font-size: 1.5rem;
            font-weight: 600;
            border-bottom: none;
            margin-bottom: 0.5rem;
        }
        .logo-laos {
            display: block;
            margin: 0 auto 18px auto;
            width: 64px;
            height: 64px;
            object-fit: contain;
            filter: drop-shadow(0 0 16px #a992f7cc);
            transition: transform 0.25s, filter 0.25s;
        }
        .logo-laos:hover {
            transform: scale(1.08) rotate(-3deg);
            filter: drop-shadow(0 0 32px #a992f7ee);
        }
        .form-label {
            color: #eaeaea;
            font-weight: 500;
        }
        .form-control {
            background: #2d3250;
            color: #eaeaea;
            border: 1.5px solid #a992f7;
            border-radius: 12px;
            box-shadow: 0 2px 8px 0 #23294633;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-size: 1.08rem;
            padding-left: 1.1rem;
        }
        .form-control:focus {
            border-color: #c1b3fa;
            box-shadow: 0 0 0 3px #a992f755;
            background: #232946;
            color: #fff;
        }
        .form-control::placeholder {
            color: #b8b8d1;
            opacity: 1;
            font-style: italic;
        }
        .btn-primary {
            background: linear-gradient(90deg, #a992f7 0%, #6c63ff 100%);
            border: none;
            color: #232946;
            font-weight: 600;
            border-radius: 10px;
            box-shadow: 0 2px 8px 0 #a992f733;
            transition: background 0.2s, transform 0.18s;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #6c63ff 0%, #a992f7 100%);
            color: #232946;
            transform: translateY(-2px) scale(1.03);
        }
        #error-message {
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header text-center">
                    <img src="/assets/imgs/laos.png" alt="Laos Logo" class="logo-laos">
                    Đăng nhập Admin
                </div>
                <div class="card-body">
                    <div id="error-message" class="alert alert-danger" style="display: none;"></div>
                    <form id="login-form">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email hoặc Username</label>
                            <input type="text" class="form-control" id="email" name="email" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const errorMessage = document.getElementById('error-message');
    const form = document.getElementById('login-form');
    const formData = new FormData(form);
    try {
        const response = await fetch('/login', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        if (response.status === 419) {
            errorMessage.textContent = 'Lỗi CSRF token. Vui lòng thử lại!';
            errorMessage.style.display = 'block';
            return;
        }
        const data = await response.json();
        if (response.ok && data.success) {
            sessionStorage.setItem('access_token', data.access_token);
            sessionStorage.setItem('user', JSON.stringify(data.user));
            window.location.href = '/';
        } else {
            errorMessage.textContent = data.message || 'Đăng nhập thất bại!';
            errorMessage.style.display = 'block';
        }
    } catch (error) {
        errorMessage.textContent = 'Có lỗi xảy ra khi đăng nhập!';
        errorMessage.style.display = 'block';
    }
});
</script>
</body>
</html> 