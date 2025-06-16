<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WISDOM GOLD Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: radial-gradient(circle, #1f2937, #111827);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(0,0,0,0.37);
            padding: 40px;
            width: 450px;
            color: #fff;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="card">
        <div class="mb-6 text-center">
            <h2 class="text-4xl font-bold mb-2">🔑 Admin Login</h2>
            <p class="text-gray-300 text-lg">WISDOM GOLD BACKEND</p>
        </div>

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block mb-2">Prefix</label>
                <input type="text" name="prefix" placeholder="Prefix" required 
                       class="w-full px-4 py-3 rounded-md bg-gray-700 focus:bg-gray-600 focus:outline-none">
            </div>

            <div class="mb-4">
                <label class="block mb-2">Username</label>
                <input type="text" name="username" placeholder="Username" required
                       class="w-full px-4 py-3 rounded-md bg-gray-700 focus:bg-gray-600 focus:outline-none">
            </div>

            <div class="mb-6">
                <label class="block mb-2">Password</label>
                <input type="password" name="password" placeholder="Password" required
                       class="w-full px-4 py-3 rounded-md bg-gray-700 focus:bg-gray-600 focus:outline-none">
            </div>

            <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white py-3 rounded-md transition duration-300 text-lg font-semibold">
                Login
            </button>

            @if ($errors->any())
                <div class="mt-4 text-red-400 text-center">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
        </form>
    </div>

</body>
</html>
