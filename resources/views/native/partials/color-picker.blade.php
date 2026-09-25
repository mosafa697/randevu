<native:text font="heading" class="text-sm font-semibold text-theme-on-surface">{{ __('randevu.color_label') }}</native:text>

<native:row class="w-full flex-wrap gap-3 items-center">
    <native:pressable @press="pickBlue" :a11y-label="__('randevu.color_blue')" class="rounded-full {{ $selected === '#2563EB' ? 'w-11 h-11 border border-theme-on-surface' : 'w-10 h-10' }} bg-[#2563EB]" />
    <native:pressable @press="pickIndigo" :a11y-label="__('randevu.color_indigo')" class="rounded-full {{ $selected === '#4F46E5' ? 'w-11 h-11 border border-theme-on-surface' : 'w-10 h-10' }} bg-[#4F46E5]" />
    <native:pressable @press="pickPurple" :a11y-label="__('randevu.color_purple')" class="rounded-full {{ $selected === '#7C3AED' ? 'w-11 h-11 border border-theme-on-surface' : 'w-10 h-10' }} bg-[#7C3AED]" />
    <native:pressable @press="pickPink" :a11y-label="__('randevu.color_pink')" class="rounded-full {{ $selected === '#DB2777' ? 'w-11 h-11 border border-theme-on-surface' : 'w-10 h-10' }} bg-[#DB2777]" />
    <native:pressable @press="pickRed" :a11y-label="__('randevu.color_red')" class="rounded-full {{ $selected === '#DC2626' ? 'w-11 h-11 border border-theme-on-surface' : 'w-10 h-10' }} bg-[#DC2626]" />
    <native:pressable @press="pickOrange" :a11y-label="__('randevu.color_orange')" class="rounded-full {{ $selected === '#EA580C' ? 'w-11 h-11 border border-theme-on-surface' : 'w-10 h-10' }} bg-[#EA580C]" />
    <native:pressable @press="pickAmber" :a11y-label="__('randevu.color_amber')" class="rounded-full {{ $selected === '#D97706' ? 'w-11 h-11 border border-theme-on-surface' : 'w-10 h-10' }} bg-[#D97706]" />
    <native:pressable @press="pickGreen" :a11y-label="__('randevu.color_green')" class="rounded-full {{ $selected === '#059669' ? 'w-11 h-11 border border-theme-on-surface' : 'w-10 h-10' }} bg-[#059669]" />
    <native:pressable @press="pickTeal" :a11y-label="__('randevu.color_teal')" class="rounded-full {{ $selected === '#0D9488' ? 'w-11 h-11 border border-theme-on-surface' : 'w-10 h-10' }} bg-[#0D9488]" />
    <native:pressable @press="pickCyan" :a11y-label="__('randevu.color_cyan')" class="rounded-full {{ $selected === '#0E7490' ? 'w-11 h-11 border border-theme-on-surface' : 'w-10 h-10' }} bg-[#0E7490]" />
    <native:pressable @press="pickBrown" :a11y-label="__('randevu.color_brown')" class="rounded-full {{ $selected === '#8C5E3C' ? 'w-11 h-11 border border-theme-on-surface' : 'w-10 h-10' }} bg-[#8C5E3C]" />
    <native:pressable @press="pickGray" :a11y-label="__('randevu.color_gray')" class="rounded-full {{ $selected === '#64748B' ? 'w-11 h-11 border border-theme-on-surface' : 'w-10 h-10' }} bg-[#64748B]" />
    <native:pressable @press="clearColor">
        <native:text class="text-sm text-theme-on-surface-variant">{{ __('randevu.color_none') }}</native:text>
    </native:pressable>
</native:row>

@if(!empty($errors['color']))
    <native:text class="text-sm text-theme-destructive">{{ $errors['color'] }}</native:text>
@endif
