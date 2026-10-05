<native:text font="heading" class="text-sm text-theme-on-surface">{{ __('randevu.color_label') }}</native:text>

<native:row class="w-full flex-wrap gap-3 items-center">
    <native:pressable @press="clearColor" :a11y-label="__('randevu.color_none')" class="w-11 h-11 rounded-full items-center justify-center border-theme-outline-variant border-2">
        <native:text class="text-base text-theme-on-surface-variant">/</native:text>
    </native:pressable>
    <native:pressable @press="pickBlue" :a11y-label="__('randevu.color_blue')" class="w-11 h-11 rounded-full items-center justify-center bg-[#2563EB] {{ $selected === '#2563EB' ? 'border-theme-on-surface border-2' : '' }}">
        @if($selected === '#2563EB')
            <native:text class="text-base text-theme-on-primary">✓</native:text>
        @endif
    </native:pressable>
    <native:pressable @press="pickIndigo" :a11y-label="__('randevu.color_indigo')" class="w-11 h-11 rounded-full items-center justify-center bg-[#4F46E5] {{ $selected === '#4F46E5' ? 'border-theme-on-surface border-2' : '' }}">
        @if($selected === '#4F46E5')
            <native:text class="text-base text-theme-on-primary">✓</native:text>
        @endif
    </native:pressable>
    <native:pressable @press="pickPurple" :a11y-label="__('randevu.color_purple')" class="w-11 h-11 rounded-full items-center justify-center bg-[#7C3AED] {{ $selected === '#7C3AED' ? 'border-theme-on-surface border-2' : '' }}">
        @if($selected === '#7C3AED')
            <native:text class="text-base text-theme-on-primary">✓</native:text>
        @endif
    </native:pressable>
    <native:pressable @press="pickPink" :a11y-label="__('randevu.color_pink')" class="w-11 h-11 rounded-full items-center justify-center bg-[#DB2777] {{ $selected === '#DB2777' ? 'border-theme-on-surface border-2' : '' }}">
        @if($selected === '#DB2777')
            <native:text class="text-base text-theme-on-primary">✓</native:text>
        @endif
    </native:pressable>
    <native:pressable @press="pickRed" :a11y-label="__('randevu.color_red')" class="w-11 h-11 rounded-full items-center justify-center bg-[#DC2626] {{ $selected === '#DC2626' ? 'border-theme-on-surface border-2' : '' }}">
        @if($selected === '#DC2626')
            <native:text class="text-base text-theme-on-primary">✓</native:text>
        @endif
    </native:pressable>
    <native:pressable @press="pickOrange" :a11y-label="__('randevu.color_orange')" class="w-11 h-11 rounded-full items-center justify-center bg-[#EA580C] {{ $selected === '#EA580C' ? 'border-theme-on-surface border-2' : '' }}">
        @if($selected === '#EA580C')
            <native:text class="text-base text-theme-on-primary">✓</native:text>
        @endif
    </native:pressable>
    <native:pressable @press="pickAmber" :a11y-label="__('randevu.color_amber')" class="w-11 h-11 rounded-full items-center justify-center bg-[#D97706] {{ $selected === '#D97706' ? 'border-theme-on-surface border-2' : '' }}">
        @if($selected === '#D97706')
            <native:text class="text-base text-theme-on-primary">✓</native:text>
        @endif
    </native:pressable>
    <native:pressable @press="pickGreen" :a11y-label="__('randevu.color_green')" class="w-11 h-11 rounded-full items-center justify-center bg-[#059669] {{ $selected === '#059669' ? 'border-theme-on-surface border-2' : '' }}">
        @if($selected === '#059669')
            <native:text class="text-base text-theme-on-primary">✓</native:text>
        @endif
    </native:pressable>
    <native:pressable @press="pickTeal" :a11y-label="__('randevu.color_teal')" class="w-11 h-11 rounded-full items-center justify-center bg-[#0D9488] {{ $selected === '#0D9488' ? 'border-theme-on-surface border-2' : '' }}">
        @if($selected === '#0D9488')
            <native:text class="text-base text-theme-on-primary">✓</native:text>
        @endif
    </native:pressable>
    <native:pressable @press="pickCyan" :a11y-label="__('randevu.color_cyan')" class="w-11 h-11 rounded-full items-center justify-center bg-[#0E7490] {{ $selected === '#0E7490' ? 'border-theme-on-surface border-2' : '' }}">
        @if($selected === '#0E7490')
            <native:text class="text-base text-theme-on-primary">✓</native:text>
        @endif
    </native:pressable>
    <native:pressable @press="pickBrown" :a11y-label="__('randevu.color_brown')" class="w-11 h-11 rounded-full items-center justify-center bg-[#8C5E3C] {{ $selected === '#8C5E3C' ? 'border-theme-on-surface border-2' : '' }}">
        @if($selected === '#8C5E3C')
            <native:text class="text-base text-theme-on-primary">✓</native:text>
        @endif
    </native:pressable>
    <native:pressable @press="pickGray" :a11y-label="__('randevu.color_gray')" class="w-11 h-11 rounded-full items-center justify-center bg-[#64748B] {{ $selected === '#64748B' ? 'border-theme-on-surface border-2' : '' }}">
        @if($selected === '#64748B')
            <native:text class="text-base text-theme-on-primary">✓</native:text>
        @endif
    </native:pressable>
</native:row>

<native:button :label="$show_custom_color ? __('randevu.color_custom_hide') : __('randevu.color_custom_label')" variant="ghost" @press="toggleCustomColor" class="w-full min-h-12" />

@if($show_custom_color)
    <native:row class="w-full gap-3 items-center">
        @if($selected !== '' && preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $selected))
            <native:pressable :bg="$selected" :a11y-label="__('randevu.color_custom_label')" class="w-11 h-11 rounded-full shrink-0" />
        @else
            <native:pressable :a11y-label="__('randevu.color_none')" class="w-11 h-11 rounded-full shrink-0 border border-theme-outline" />
        @endif
        <native:text class="text-sm text-theme-on-surface-variant">{{ $selected !== '' ? $selected : __('randevu.color_none') }}</native:text>
    </native:row>

    <native:row class="w-full gap-2 items-center">
        <native:text class="text-sm text-theme-on-surface-variant w-16 shrink-0">{{ __('randevu.color_channel_red') }}</native:text>
        <native:slider min="0" max="255" step="1" native:model="color_r" :a11y-label="__('randevu.color_channel_red')" class="flex-1" />
    </native:row>

    <native:row class="w-full gap-2 items-center">
        <native:text class="text-sm text-theme-on-surface-variant w-16 shrink-0">{{ __('randevu.color_channel_green') }}</native:text>
        <native:slider min="0" max="255" step="1" native:model="color_g" :a11y-label="__('randevu.color_channel_green')" class="flex-1" />
    </native:row>

    <native:row class="w-full gap-2 items-center">
        <native:text class="text-sm text-theme-on-surface-variant w-16 shrink-0">{{ __('randevu.color_channel_blue') }}</native:text>
        <native:slider min="0" max="255" step="1" native:model="color_b" :a11y-label="__('randevu.color_channel_blue')" class="flex-1" />
    </native:row>
@endif

@if(!empty($errors['color']))
    <native:text class="text-sm text-theme-destructive">{{ $errors['color'] }}</native:text>
@endif
