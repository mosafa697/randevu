@php($previewPhrase = $previewPhrase ?? null)
<native:column class="w-full gap-1">
    <native:text font="heading" class="text-sm font-semibold text-theme-on-surface">{{ __('randevu.preview_label') }}</native:text>
    <native:text class="text-sm text-theme-on-surface-variant">{{ $previewPhrase ?? __('randevu.preview_invalid') }}</native:text>
</native:column>
