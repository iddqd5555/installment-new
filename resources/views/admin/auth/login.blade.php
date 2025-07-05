<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white shadow-lg rounded-lg p-10 w-full max-w-md">
        <h3 class="text-center text-2xl font-bold text-yellow-700 mb-8">WISDOM GOLD ADMIN LOGIN</h3>

        @if(session('error'))
            <div class="mb-4 text-red-600 text-center text-sm">{{ session('error') }}</div>
        @endif

        <form action="{{ route('filament.admin.auth.attempt') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block font-semibold">Prefix:</label>
                <input type="text" name="prefix" required class="w-full p-2 border rounded">
            </div>
            <div class="mb-4">
                <label class="block font-semibold">Username:</label>
                <input type="text" name="username" required class="w-full p-2 border rounded">
            </div>
            <div class="mb-4">
                <label class="block font-semibold">Password:</label>
                <input type="password" name="password" required class="w-full p-2 border rounded">
            </div>
            <button type="submit" class="w-full bg-yellow-500 text-white py-2 rounded hover:bg-yellow-600 font-bold shadow">
                เข้าสู่ระบบ
            </button>
        </form>
    </div>
</body>
</html>
