@php($previewPhrase = $previewPhrase ?? null)
<native:column class="w-full gap-1">
    <native:text font="heading" class="text-sm text-theme-on-surface">{{ __('randevu.preview_label') }}</native:text>
    <native:column class="w-full p-4 gap-1 rounded-2xl bg-theme-surface border border-theme-outline-variant">
        @if($previewPhrase !== null)
            <native:text class="text-base text-theme-on-surface leading-relaxed">{{ $previewPhrase }}</native:text>
        @else
            <native:text class="text-sm text-theme-on-surface-variant leading-relaxed">{{ __('randevu.preview_invalid') }}</native:text>
        @endif
    </native:column>
</native:column>
