@props(['user', 'size' => 9])
<span class="inline-grid shrink-0 place-items-center rounded-full font-display text-sm font-bold text-white" style="background:{{ $user->avatar_color }};width:{{ $size * 4 }}px;height:{{ $size * 4 }}px">{{ $user->initials }}</span>
