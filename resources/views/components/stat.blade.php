@props(['label', 'value', 'suffix' => '', 'tone' => 'blue', 'i' => 0, 'decimals' => 0])
@php $tones = ['blue' => 'from-blue-500 to-blue-600', 'red' => 'from-rose-500 to-red-600', 'green' => 'from-emerald-500 to-green-600', 'violet' => 'from-violet-500 to-purple-600', 'amber' => 'from-amber-400 to-orange-500']; @endphp
<div class="card fade-up relative overflow-hidden p-4 sm:p-5" style="--i:{{ $i }}">
    <span class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-gradient-to-br {{ $tones[$tone] }} opacity-15"></span>
    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 sm:text-xs sm:tracking-wider">{{ $label }}</p>
    <p class="mt-1.5 font-display text-2xl font-bold sm:mt-2 sm:text-3xl" x-data="{ n: 0, t: {{ $value }} }" x-init="let s = performance.now(); const f = (now) => { const p = Math.min((now - s) / 900, 1); n = t * p; if (p < 1) requestAnimationFrame(f); }; requestAnimationFrame(f)"><span x-text="n.toFixed({{ $decimals }})">0</span>{{ $suffix }}</p>
</div>
