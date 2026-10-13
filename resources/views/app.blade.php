<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    @vite(['resources/js/app.js'])
    @inertiaHead
    <script>
        // Initialize theme from localStorage
        const theme = localStorage.getItem('appearance') || 'system';
        if (theme === 'system') {
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.add('light');
            }
        } else {
            document.documentElement.classList.add(theme);
        }
    </script>

</head>

<body class="antialiased bg-gray-100 dark:bg-gray-900">
    @inertia
    <script>
        window.Telegram.WebApp.expand();
    </script>
</body>

</html>