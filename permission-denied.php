<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Unauthorized</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdn.jsdelivr.net/npm">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="./src/output.css">
    <style>
        /* Custom fade + scale animation */
        @keyframes fadeScale {
            0% {
                opacity: 0;
                transform: scale(0.95);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .fade-in {
            animation: fadeScale 0.8s ease-out forwards;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-900">

    <div class="fade-in text-center max-w-md p-8 rounded-2xl shadow-xl bg-gray-700">
        <div class="mb-4">
            <svg class="w-16 h-16 text-red-500 mx-auto" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856C19.09 
                 19 20 18.09 20 16.938V7.062C20 5.91 
                 19.09 5 17.938 5H6.062C4.91 5 4 5.91 
                 4 7.062v9.876C4 18.09 4.91 19 
                 6.062 19z" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-white mb-2">
            Unauthorized
        </h1>
        <p class="text-white mb-6">
            You do not have permission to access this page.
        </p>
        <a href="/"
            class="inline-block px-6 py-2 rounded-lg bg-blue-600 text-white 
              hover:bg-blue-700 transition-colors duration-300">
            Back Home
        </a>
    </div>

</body>

</html>