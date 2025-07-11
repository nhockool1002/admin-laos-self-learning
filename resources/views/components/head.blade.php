<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Học Tiếng Lào Admin Panel')</title>
    <link rel="icon" type="image/png" href="/assets/imgs/laos.png">
    <link rel="stylesheet" href="/assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('head')
    <style>
        /* Xoá import font và style body ở đây, đã dùng chung qua style.css */
        .toast-alert {
            position: fixed;
            top: 32px;
            right: 32px;
            z-index: 9999;
            min-width: 240px;
            max-width: 340px;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-size: 1.08rem;
            font-weight: 600;
            box-shadow: 0 4px 24px 0 #23294655;
            opacity: 0.98;
            display: none;
            transition: all 0.25s;
        }
        .toast-success {
            background: linear-gradient(90deg, #4ade80 0%, #22d3ee 100%);
            color: #232946;
        }
        .toast-failed {
            background: linear-gradient(90deg, #ff5252 0%, #ffb199 100%);
            color: #fff;
        }
    </style>
</head> 