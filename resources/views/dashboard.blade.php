@extends('layouts.app')

@section('content')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div class="h-[calc(100vh-64px)] bg-[#f8fafc] p-4 md:p-8 overflow-hidden" x-data="{ tab: 'expiry' }">
    <div class="max-w-7xl mx-auto h-full flex flex-col">
        
        <div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-6 shrink-0">
            <div>
                <span class="text-blue-600 font-black tracking-widest text-[10px] uppercase italic">Inventory Intelligence</span>
                <h1 class="text-3xl font-black text-slate-900 leading-none">แผงควบคุมสต็อกยา</h1>
            </div>
            
            <div class="flex p-1 bg-slate-200/50 backdrop-blur-md rounded-2xl w-fit border border-white shadow-inner">
                <button @click="tab = 'expiry'" :class="tab === 'expiry' ? 'bg-white shadow-md text-blue-600' : 'text-slate-500'"
                    class="px-5 py-2 rounded-xl font-bold transition-all flex items-center gap-2 text-xs">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span> วันหมดอายุ
                </button>
                <button @click="tab = 'min'" :class="tab === 'min' ? 'bg-white shadow-md text-red-600' : 'text-slate-500'"
                    class="px-5 py-2 rounded-xl font-bold transition-all flex items-center gap-2 text-xs">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span> สต็อกต่ำ
                </button>
                <button @click="tab = 'max'" :class="tab === 'max' ? 'bg-white shadow-md text-purple-600' : 'text-slate-500'"
                    class="px-5 py-2 rounded-xl font-bold transition-all flex items-center gap-2 text-xs">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span> สต็อกเกิน
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
    <div x-show="tab === 'expiry'" x-transition:enter="transition ease-out duration-300 opacity-0" x-transition:enter-end="opacity-100">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($expiryAlerts as $item)
            <div class="group bg-white rounded-3xl p-5 border border-slate-100 shadow-sm hover:shadow-xl transition-all relative overflow-hidden" x-data="{ showLots: false }">
                <div class="absolute top-0 right-0 w-16 h-16 bg-blue-50/50 rounded-bl-full -mr-4 -mt-4"></div>
                
                <div class="relative">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex flex-col gap-1">
                            @if($item->days_left < 0)
                                <span class="bg-red-600 text-white px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest shadow-md w-fit animate-pulse">
                                    หมดอายุไปแล้ว {{ abs($item->days_left) }} วัน
                                </span>
                            @elseif($item->days_left == 0)
                                <span class="bg-orange-600 text-white px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest shadow-md w-fit">
                                    หมดอายุวันนี้
                                </span>
                            @else
                                <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest shadow-md w-fit">
                                    อีก {{ $item->days_left }} วัน
                                </span>
                            @endif

                            @if($item->total_lots > 1)
                                <span class="text-[9px] font-bold text-orange-500 italic">* รวม {{ $item->total_lots }} ล็อต</span>
                            @endif
                        </div>
                        <span class="text-[9px] font-bold text-slate-300 font-mono text-right uppercase">
                            LOT ใกล้สุด: #{{ $item->lot_number }}
                        </span>
                    </div>

                    <h3 class="text-base font-black text-slate-800 mb-1 line-clamp-1 italic">{{ $item->name }}</h3>
                    
                    <p class="text-[10px] text-slate-400 font-bold mb-4 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/center" class="h-3 w-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        ล็อตแรกหมด: {{ \Carbon\Carbon::parse($item->expire_date)->format('d M Y') }}
                    </p>

                    <div class="flex items-end justify-between bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter italic">Total In Stock</p>
                            <p class="text-xl font-black text-slate-900 leading-none">
                                {{ number_format($item->total_quantity) }} 
                                <span class="text-[10px] text-slate-400 italic">{{ $item->unit_name }}</span>
                            </p>
                        </div>
                        <button @click="showLots = true" class="p-2 bg-blue-600 rounded-xl shadow-lg text-white hover:bg-blue-700 transition-all scale-110">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                    </div>
                </div>

                <template x-teleport="body">
                    <div x-show="showLots" class="fixed inset-0 z-[99] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
                        <div @click.away="showLots = false" class="bg-white rounded-[40px] shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
                            <div class="p-8">
                                <div class="flex justify-between items-center mb-6">
                                    <h2 class="text-xl font-black text-slate-800 italic">{{ $item->name }}</h2>
                                    <button @click="showLots = false" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
                                </div>
                                
                                <div class="space-y-3">
                                    @foreach($item->all_lots as $index => $lot)
                                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-3xl border border-slate-100">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px] font-black italic">
                                                L{{ $index + 1 }}
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase leading-none mb-1">Lot: {{ $lot->lot_number }}</p>
                                                <p class="text-[11px] font-black text-slate-700">EXP: {{ \Carbon\Carbon::parse($lot->expire_date)->format('d/m/Y') }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-lg font-black text-blue-600 leading-none">{{ number_format($lot->quantity) }}</p>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase italic">{{ $item->unit_name }}</p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <div class="mt-8 pt-6 border-t border-dashed border-slate-200 flex justify-between items-center">
                                    <span class="text-xs font-black text-slate-400 italic">TOTAL SUM</span>
                                    <span class="text-2xl font-black text-slate-900">{{ number_format($item->total_quantity) }} {{ $item->unit_name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            @empty
                <div class="col-span-full py-20 text-center opacity-40 italic">ไม่มียาใกล้หมดอายุ</div>
            @endforelse
        </div>
    </div>


            <div x-show="tab === 'min'" x-transition class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($minAlerts as $med)
                <div class="group bg-white rounded-3xl p-5 border border-red-50 flex items-center justify-between shadow-sm hover:shadow-xl transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-red-50 text-red-500 rounded-xl flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-width="2.5" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-800 line-clamp-1 italic">{{ $med->name }}</h3>
                            <p class="text-[9px] font-bold text-red-400 uppercase tracking-widest">Min: {{ number_format($med->min_stock) }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-black text-red-600 leading-none">{{ number_format($med->stock_master) }}</p>
                        <p class="text-[8px] font-black text-slate-300 uppercase mt-1 italic">คงเหลือรวม</p>
                    </div>
                </div>
                @empty
                    <div class="col-span-full py-20 text-center opacity-40 text-sm font-bold italic">สต็อกปกติทุกรายการ</div>
                @endforelse
            </div>

            <div x-show="tab === 'max'" x-transition class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($maxAlerts as $med)
                <div class="group bg-white rounded-3xl p-5 border border-purple-50 flex items-center justify-between shadow-sm hover:shadow-xl transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-width="2.5" d="M5 11l7-7 7 7M5 19l7-7 7 7" /></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-800 line-clamp-1 italic">{{ $med->name }}</h3>
                            <p class="text-[9px] font-bold text-purple-400 uppercase tracking-widest">Max: {{ number_format($med->max_stock) }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-black text-purple-600 leading-none">{{ number_format($med->stock_master) }}</p>
                        <p class="text-[8px] font-black text-slate-300 uppercase mt-1 italic">เกินสต็อก</p>
                    </div>
                </div>
                @empty
                    <div class="col-span-full py-20 text-center opacity-40 text-sm font-bold italic">ไม่มีรายการสต็อกเกิน</div>
                @endforelse
            </div>

        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    body { font-family: 'Kanit', sans-serif; }
</style>
@endsection