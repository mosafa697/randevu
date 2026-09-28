<native:text font="heading" class="text-sm font-semibold text-theme-on-surface">{{ __('randevu.cover_label') }}</native:text>

@php($coverSrc = \App\Services\CoverImage::src($cover_path ?? null))
@if($coverSrc !== null)
    <native:image :src="$coverSrc" class="w-full rounded-xl" :height="180" :fit="2" />
@endif

<native:row class="w-full gap-2">
    <native:button :label="__('randevu.cover_pick')" @press="pickCover" />
    @if(!empty($cover_path))
        <native:button :label="__('randevu.cover_remove')" @press="removeCover" variant="destructive" />
    @endif
</native:row>

@if(!empty($errors['cover']))
    <native:text class="text-sm text-theme-destructive">{{ $errors['cover'] }}</native:text>
@endif
