<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ระบบจัดการคลังยา')</title>
    
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script defer src="{{ asset('js/alpine-collapse.min.js') }}"></script>
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
    <script src="{{ asset('js/tailwind.js') }}"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('css/font-awesome/all.min.css') }}">

    <style>
        body {
            zoom: 1.25 !important;
            -moz-transform: scale(1.25);
            -moz-transform-origin: top left;
            
            /* 🟢 เพิ่ม 2 บรรทัดนี้เพื่อจำกัดความสูงและความกว้างไม่ให้ล้นหน้าจอ */
            height: calc(100vh / 1.25) !important;
            width: calc(100vw / 1.25) !important;
        }
        @font-face {
            font-family: 'Sarabun';
            src: url("{{ asset('fonts/Sarabun-Regular.ttf') }}") format('truetype');
            font-weight: 400;
            font-style: normal;
        }
        @font-face {
            font-family: 'Sarabun';
            src: url("{{ asset('fonts/Sarabun-Bold.ttf') }}") format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        body { font-family: 'Sarabun', sans-serif; letter-spacing: -0.01em; }
        [x-cloak] { display: none !important; }
        
        .sidebar-item { transition: all 0.2s ease; border-left: 4px solid transparent; }
        .sidebar-active { 
            background: linear-gradient(to right, #eff6ff, #ffffff);
            border-left-color: #2563eb; 
            color: #1e40af; 
            font-weight: 600;
        }
        
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #d1d5db; }

        /* 🟢 การซ่อน/แสดง Sidebar ให้นุ่มนวล */
        #sidebar {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: width;
        }

        /* 🟢 เมื่อหดตัว ให้ซ่อนตัวหนังสือและการจัดแต่งทั้งหมดที่ไม่จำเป็น */
        .sidebar-collapsed span, 
        .sidebar-collapsed p,
        .sidebar-collapsed h1,
        .sidebar-collapsed .user-info {
            display: none !important;
        }

        /* 🟢 ปรับลด Padding ของกล่องนอกสุด และจัดสัญลักษณ์โลโก้ให้อยู่กึ่งกลางพอดี */
        .sidebar-collapsed .logo-box {
            padding-top: 2rem !important;
            padding-bottom: 2rem !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
            justify-content: center !important;
        }
        
        /* 🟢 สั่งซ่อนกล่องขาวใบใหญ่ที่มีไอคอนเม็ดยาตอนหดตัวเพื่อไม่ให้ก้นกล่องล้น */
        .sidebar-collapsed .logo-card {
            display: none !important; 
        }
        
        /* สั่งซ่อนเส้นคั่น */
        .sidebar-collapsed .divider-line {
            display: none !important;
        }
        
        /* ดันไอคอนเมนูให้อยู่กึ่งกลางพอดีตอนแถบแคบ */
        .sidebar-collapsed .sidebar-item {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            border-left-width: 0px !important;
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-gray-800 flex h-screen overflow-hidden">

    <aside id="sidebar" 
           onmouseenter="expandSidebar()" 
           onmouseleave="collapseSidebar()"
           class="w-72 bg-white border-r border-gray-100 flex-shrink-0 hidden md:flex flex-col shadow-sm overflow-hidden">
        
        <div class="px-8 pt-12 pb-8 flex flex-col items-center group logo-box transition-all">
            <div class="relative">
                <img src="{{ asset('images/BRH.png') }}" 
                     alt="BRH Logo" 
                     class="h-10 w-auto mb-4 object-contain transition-transform duration-500 group-hover:scale-105">
            </div>
            
            <div class="logo-card relative py-8 px-6 bg-white rounded-2xl shadow-sm border border-gray-100 max-w-sm mx-auto overflow-hidden w-full">
                <div class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 bg-blue-50 rounded-full blur-3xl opacity-50"></div>
                
                <div class="relative text-center">
                    <div class="mb-3 inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 shadow-lg shadow-blue-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <g transform="rotate(-45 12 12)">
                                <path d="M9 12V16C9 17.6569 10.3431 19 12 19C13.6569 19 15 17.6569 15 16V12H9Z" fill="white" />
                                <rect x="9" y="5" width="6" height="14" rx="3" stroke-linecap="round" stroke-linejoin="round" />
                                <line x1="9" y1="12" x2="15" y2="12" />
                            </g>
                        </svg>
                    </div>

                    <h1 class="text-2xl font-extrabold text-gray-800 tracking-tight leading-none">
                        Inventory <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">Medicine</span>
                        <div class="text-sm font-medium text-gray-400 mt-1 uppercase tracking-widest">System</div>
                    </h1>

                    <div class="flex items-center justify-center gap-3 mt-5">
                        <span class="h-[1px] flex-1 bg-gradient-to-r from-transparent to-blue-200"></span>
                        <div class="px-3 py-1 rounded-full bg-blue-50 border border-blue-100">
                            <p class="text-[10px] font-bold text-blue-500 uppercase tracking-[0.25em]">BRH Network</p>
                        </div>
                        <span class="h-[1px] flex-1 bg-gradient-to-l from-transparent to-blue-200"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-10 divider-line">
            <div class="h-px bg-gradient-to-r from-transparent via-gray-100 to-transparent"></div>
        </div>
            
        <nav class="flex-1 px-3 space-y-1 overflow-y-auto mt-4">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest px-5 py-4">Menu Selection</p>
            
            <a href="{{ route('pharmacy.dashboard') }}" class="sidebar-item flex items-center gap-4 px-5 py-3 rounded-xl {{ request()->is('pharmacy/dashboard') ? 'sidebar-active' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                <i class="fas fa-cubes w-5 text-lg flex-shrink-0 text-center"></i> <span>Dashboard</span>
            </a>

            <a href="{{ route('pharmacy.index') }}" class="sidebar-item flex items-center gap-4 px-5 py-3 rounded-xl {{ request()->is('pharmacy') ? 'sidebar-active' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                <i class="fas fa-prescription-bottle-alt w-5 text-lg flex-shrink-0 text-center"></i> <span>จ่ายยา (Pharmacy)</span>
            </a>

            <a href="{{ route('patients.index') }}" class="sidebar-item flex items-center gap-4 px-5 py-3 rounded-xl {{ request()->is('patients*') ? 'sidebar-active' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                <i class="fas fa-user-injured w-5 text-lg flex-shrink-0 text-center"></i> <span>รายชื่อคนไข้</span>
            </a>

            <a href="{{ route('transfers.index') }}" class="sidebar-item flex items-center gap-4 px-5 py-3 rounded-xl {{ request()->is('transfers*') ? 'sidebar-active' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                <i class="fas fa-box-archive w-5 text-lg flex-shrink-0 text-center"></i> <span>คลังยา (Inventory)</span>
            </a>

            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest px-5 py-6">Configurations</p>

            <a href="{{ route('units.index') }}" class="sidebar-item flex items-center gap-4 px-5 py-3 rounded-xl {{ request()->routeIs('units.*') ? 'sidebar-active' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                <i class="fas fa-tags w-5 text-lg flex-shrink-0 text-center"></i> <span>จัดการหน่วยนับยา</span>
            </a>

            <a href="{{ route('staff.index') }}" class="sidebar-item flex items-center gap-4 px-5 py-3 rounded-xl text-gray-500 hover:bg-gray-50 hover:text-gray-700">
                <i class="fas fa-user-md w-5 text-lg flex-shrink-0 text-center"></i> <span>จัดการรายชื่อ Staff</span>
            </a>
            <a href="{{ route('categories.index') }}" class="sidebar-item flex items-center gap-4 px-5 py-3 rounded-xl {{ request()->routeIs('categories.*') ? 'sidebar-active' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                <i class="fas fa-layer-group w-5 text-lg flex-shrink-0 text-center"></i> <span>จัดการหมวดหมู่ยา</span>
            </a>
            
            <a href="{{ route('reports.barcode') }}" class="sidebar-item flex items-center gap-4 px-5 py-3 rounded-xl {{ request()->is('reports/barcode*') ? 'sidebar-active' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                <i class="fas fa-barcode w-5 text-lg flex-shrink-0 text-center"></i> <span>พิมพ์บาร์โค้ดยา</span>
            </a>

            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest px-5 py-6">Insights</p>
            
            <a href="{{ route('reports.inventory') }}" class="sidebar-item flex items-center gap-4 px-5 py-3 rounded-xl {{ request()->is('reports/inventory*') ? 'sidebar-active' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                <i class="fas fa-file-waveform w-5 text-lg flex-shrink-0 text-center"></i> <span>รายงานสต็อกคงเหลือ</span>
            </a>

            <a href="{{ route('reports.dispensing') }}" class="sidebar-item flex items-center gap-4 px-5 py-3 rounded-xl {{ request()->is('reports/dispensing*') ? 'sidebar-active' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                <i class="fas fa-clipboard-list w-5 text-lg flex-shrink-0 text-center"></i> <span>รายงานการเบิกจ่าย</span>
            </a>

            <a href="{{ route('pharmacy.analytics') }}" class="sidebar-item flex items-center gap-4 px-5 py-3 rounded-xl {{ request()->routeIs('pharmacy.analytics') ? 'sidebar-active' : 'text-gray-500 hover:bg-purple-50/60 hover:text-purple-600' }}">
                <i class="fas fa-brain w-5 text-lg flex-shrink-0 text-center {{ request()->routeIs('pharmacy.analytics') ? 'text-blue-600' : 'text-purple-500' }}"></i> 
                <span>วิเคราะห์คลังยา (AI)</span>
            </a>

            <a href="{{ route('pharmacy.import.view') }}" class="sidebar-item flex items-center gap-4 px-5 py-3 rounded-xl {{ request()->is('pharmacy/import*') ? 'sidebar-active' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                <i class="fas fa-cloud-arrow-up w-5 text-lg flex-shrink-0 text-center"></i> <span>นำเข้าข้อมูล (Import)</span>
            </a>
        </nav>

        <div class="p-3 m-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-center transition-all duration-300">
            <div class="flex items-center gap-3 w-full justify-center md:justify-start">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold shadow-md shadow-blue-100 flex-shrink-0">AD</div>
                <div class="flex-1 min-w-0 user-info">
                    <p class="text-sm font-bold text-gray-900 truncate">Admin User</p>
                    <p class="text-[11px] text-gray-500 font-medium">Pharmacist</p>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <header class="h-20 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-6 md:px-10 flex-shrink-0 z-10">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="md:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                    <i class="fas fa-bars-staggered text-xl"></i>
                </button>
                <h2 class="text-xl font-bold text-gray-800 tracking-tight">
                    @yield('title', 'ระบบจัดการคลังยา')
                </h2>
            </div>

            <div class="flex items-center gap-3">
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="group relative p-2.5 bg-gray-50 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all duration-300 focus:outline-none">
                        <i class="fas fa-bell text-lg"></i>
                        @php 
                            $totalAlerts = ($nearExpiredCount ?? 0) + ($minStockCount ?? 0) + ($maxStockCount ?? 0);
                        @endphp
                        @if($totalAlerts > 0)
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center border-2 border-white font-bold animate-bounce">
                                {{ $totalAlerts }}
                            </span>
                        @endif
                    </button>

                    <div x-show="open" 
                         x-cloak
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="absolute right-0 mt-4 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 z-50 overflow-hidden">
                        
                        <div class="px-5 py-4 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                            <h3 class="text-sm font-black text-gray-800">การแจ้งเตือน</h3>
                            <span class="text-[10px] bg-blue-600 text-white px-2 py-0.5 rounded-full font-bold uppercase tracking-tighter">{{ $totalAlerts }} ใหม่</span>
                        </div>

                        <div class="max-h-[400px] overflow-y-auto p-2">
                            @if(isset($expireList) && $expireList->count() > 0)
                                @foreach($expireList as $item)
                                    <div class="flex items-start gap-3 p-3 hover:bg-red-50 rounded-xl transition-colors mb-1 border-b border-gray-50 last:border-0">
                                        <div class="p-2 bg-red-100 rounded-lg text-red-600">
                                            <i class="fas fa-clock text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="text-xs font-bold text-gray-900">{{ $item->name }}</p>
                                                    <p class="text-[9px] text-gray-500 font-medium">Lot: {{ $item->lot_number }}</p>
                                                </div>
                                                <span class="text-[9px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-bold">
                                                    เหลือ: {{ number_format($item->lot_stock) }}
                                                </span>
                                            </div>
                                            <p class="text-[10px] text-red-600 font-semibold mt-1">
                                                {{ $item->alert_msg }}
                                                <span class="text-gray-400 font-normal">({{ \Carbon\Carbon::parse($item->expire_date)->format('d/m/Y') }})</span>
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            @if(isset($minList) && count($minList) > 0)
                                @foreach($minList as $item)
                                    <div class="flex items-start gap-3 p-3 hover:bg-orange-50 rounded-xl transition-colors mb-1">
                                        <div class="p-2 bg-orange-100 rounded-lg text-orange-600"><i class="fas fa-triangle-exclamation text-sm"></i></div>
                                        <div class="flex-1 text-xs">
                                            <p class="font-bold text-gray-900">{{ $item->name }}</p>
                                            <p class="text-orange-600 font-medium">สต็อกต่ำ: {{ number_format($item->stock_master) }} ชิ้น</p>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            @if(isset($maxList) && count($maxList) > 0)
                                @foreach($maxList as $item)
                                    <div class="flex items-start gap-3 p-3 hover:bg-blue-50 rounded-xl transition-colors mb-1">
                                        <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                                            <i class="fas fa-box-open text-sm"></i>
                                        </div>
                                        <div class="flex-1 text-xs">
                                            <p class="font-bold text-gray-900">{{ $item->name }}</p>
                                            <p class="text-blue-600 font-medium">สต็อกเกินเกณฑ์: {{ number_format($item->stock_master) }} ชิ้น</p>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            @if(($totalAlerts ?? 0) == 0)
                                <div class="py-10 text-center">
                                    <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-check-double text-green-500 text-2xl"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-800">ทุกอย่างเรียบร้อยดี!</p>
                                    <p class="text-[11px] text-gray-400">ยังไม่มีรายการแจ้งเตือนในขณะนี้</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="h-8 w-[1px] bg-gray-100 mx-2"></div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-xl transition-all duration-300 font-bold text-xs group">
                        <i class="fas fa-power-off group-hover:rotate-90 transition-transform"></i>
                        <span class="hidden sm:inline">ออกจากระบบ</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 overflow-hidden flex flex-col">
            <div class="flex-1 overflow-y-auto p-6 md:p-10">
                <div class="max-w-7xl mx-auto min-h-full flex flex-col">
                    <div class="flex-1">
                        @yield('content')
                    </div>
                </div>
            </div>
            
            <footer class="py-6 border-t border-gray-100 bg-white/50 backdrop-blur-sm text-center shrink-0">
                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-[0.2em]">
                    &copy; 2026 Inventory Medicine System — <span class="text-gray-600">Anurak Butsri PCG3 BRH-Network</span>
                </p>
            </footer>
        </main>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');

        document.addEventListener("DOMContentLoaded", function() {
            collapseSidebar();
        });

        function expandSidebar() {
            if (window.innerWidth >= 768) {
                sidebar.style.width = "18rem"; // ขนาด w-72 ปกติ
                sidebar.classList.remove('sidebar-collapsed');
            }
        }

        function collapseSidebar() {
            if (window.innerWidth >= 768) {
                sidebar.style.width = "4.5rem"; // ขนาดแคบแสดงเฉพาะไอคอน
                sidebar.classList.add('sidebar-collapsed');
            }
        }

        function toggleSidebar() {
            sidebar.classList.toggle('hidden');
        }
    </script>
</body>
</html>