@props(['n', 'class' => 'h-5 w-5'])
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($n)
@case('dash')<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>@break
@case('ticket')<rect x="3" y="6" width="18" height="12" rx="2"/><path d="M9 6v12" stroke-dasharray="2 2"/>@break
@case('users')<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.5-4 3.5-6 6.5-6s6 2 6.5 6"/><circle cx="17" cy="9" r="2.5"/>@break
@case('chart')<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>@break
@case('cog')<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"/>@break
@case('plus')<path d="M12 5v14M5 12h14"/>@break
@case('bell')<path d="M6 17h12l-1.5-2.5V10a4.5 4.5 0 00-9 0v4.5z"/><path d="M10 20a2 2 0 004 0"/>@break
@case('sun')<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M5 5l1.5 1.5M17.5 17.5L19 19M19 5l-1.5 1.5M6.5 17.5L5 19"/>@break
@case('moon')<path d="M20 14.5A8 8 0 019.5 4a8 8 0 1010.5 10.5z"/>@break
@case('menu')<path d="M4 7h16M4 12h16M4 17h16"/>@break
@case('search')<circle cx="11" cy="11" r="6.5"/><path d="M20 20l-4-4"/>@break
@case('x')<path d="M6 6l12 12M18 6L6 18"/>@break
@case('check')<path d="M5 12.5l4.5 4.5L19 7"/>@break
@case('send')<path d="M21 3L10 14M21 3l-7 18-4-7-7-4z"/>@break
@case('clip')<path d="M20 11.5l-8 8a5 5 0 01-7-7l9-9a3.5 3.5 0 015 5l-9 9a2 2 0 01-3-3l8-8"/>@break
@case('download')<path d="M12 4v11M7 11l5 5 5-5M5 20h14"/>@break
@case('tag')<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8.5" r="1"/>@break
@case('shield')<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M9 12l2 2 4-4"/>@break
@case('logout')<path d="M14 4h5v16h-5M10 8l-4 4 4 4M6 12h10"/>@break
@case('alert')<path d="M12 4l9 16H3z"/><path d="M12 9v5M12 17.5v.5"/>@break
@case('filter')<path d="M4 5h16l-6 7v6l-4 2v-8z"/>@break
@case('building')<path d="M4 21V5a1 1 0 011-1h9a1 1 0 011 1v16M15 9h4a1 1 0 011 1v11M8 8h3M8 12h3M8 16h3M2 21h20"/>@break
@case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
@case('message')<path d="M21 12a8.5 8.5 0 01-8.5 8.5H5l-2 2v-6.2A8.5 8.5 0 1121 12z"/><path d="M8 12h.01M12 12h.01M16 12h.01"/>@break
@case('chevron-left')<path d="M15 6l-6 6 6 6"/>@break
@case('chevron-right')<path d="M9 6l6 6-6 6"/>@break
@case('chevron-down')<path d="M6 9l6 6 6-6"/>@break
@case('user')<circle cx="12" cy="8" r="4"/><path d="M4 21c.7-4.5 4-7 8-7s7.3 2.5 8 7"/>@break
@case('inbox')<path d="M4 4h16v16H4z"/><path d="M4 13h4l2 3h4l2-3h4"/>@break
@endswitch
</svg>
