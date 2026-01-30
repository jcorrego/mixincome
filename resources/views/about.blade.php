<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>About - MixIncome</title>
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" media="(prefers-color-scheme: light)">
    <link rel="icon" type="image/svg+xml" href="/favicon-dark.svg" media="(prefers-color-scheme: dark)">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
    <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6">
        <nav class="flex items-center justify-between gap-4">
            <a
                href="/"
                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal"
            >
                Home
            </a>
        </nav>
    </header>

    <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
        <main class="flex max-w-[335px] w-full flex-col lg:max-w-4xl">
            <div class="text-[13px] leading-[20px] p-6 pb-12 lg:p-20 bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg">
                <div class="flex justify-center mb-6">
                    <img
                        src="/images/logo-color.svg"
                        alt="MixIncome"
                        class="h-14 dark:hidden"
                    >
                    <img
                        src="/images/logo-white.svg"
                        alt="MixIncome"
                        class="h-14 hidden dark:block"
                    >
                </div>

                <h1 class="mb-4 text-xl font-medium text-center">About MixIncome</h1>

                <p class="mb-4 text-[#706f6c] dark:text-[#A1A09A]">
                    MixIncome is a platform designed to help you track and manage multiple income streams in one place.
                    Whether you earn from freelancing, investments, or side projects, MixIncome gives you a clear picture of your financial landscape.
                </p>

                <p class="text-[#706f6c] dark:text-[#A1A09A]">
                    Built with Laravel, MixIncome aims to provide a simple yet powerful way to understand where your money comes from and how it grows over time.
                </p>
            </div>
        </main>
    </div>
</body>
</html>
