<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบคลังยา</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 mx-4">
        <div class="text-center mb-8">
            <div class="bg-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.628.282a2 2 0 01-1.806 0l-.628-.282a6 6 0 00-3.86-.517l-2.387.477a2 2 0 00-1.022.547l-.54.54a2 2 0 000 2.828l1.246 1.246a2 2 0 002.828 0l.54-.54a2 2 0 00.547-1.022l.477-2.387a6 6 0 00-.517-3.86l-.282-.628a2 2 0 010-1.806l.282-.628a6 6 0 00.517-3.86l-.477-2.387a2 2 0 00-.547-1.022l-.54-.54a2 2 0 00-2.828 0L3.518 5.63a2 2 0 000 2.828l.54.54z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Inventory Medicine System</h1>
            <p class="text-slate-500">กรุณาเข้าสู่ระบบเพื่อใช้งาน</p>
        </div>

        @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Username</label>
                <input type="text" name="username" 
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                    placeholder="กรอกชื่อผู้ใช้งาน" required autofocus>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                <input type="password" name="password" 
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                    placeholder="กรอกรหัสผ่าน" required>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="mr-2 rounded border-slate-300"> จดจำฉันไว้
                </label>
            </div>

            <button type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                เข้าสู่ระบบ
            </button>
        </form>

        <div class="mt-8 text-center">
            <p class="text-xs text-slate-400">© 2026 Pharmacy Management System</p>
        </div>
    </div>

</body>
</html>