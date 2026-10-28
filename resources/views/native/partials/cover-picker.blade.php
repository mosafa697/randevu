<native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface">{{ __('randevu.cover_label') }}</native:text>

@php($coverSrc = \App\Services\CoverImage::src($cover_path ?? null))
@if($coverSrc !== null)
    <native:image :src="$coverSrc" :alt="__('randevu.cover_alt')" class="w-full rounded-xl" :height="180" :fit="2" />
@endif

<native:row class="w-full gap-2">
    <native:button :label="__('randevu.cover_pick')" variant="ghost" @press="pickCover" ios-icon="photo" android-icon="image" class="flex-1 min-h-12" />
    @if(!empty($cover_path))
        <native:button :label="__('randevu.cover_remove')" variant="ghost" ios-icon="trash" android-icon="delete" @press="removeCover" class="flex-1 min-h-12" />
    @endif
</native:row>

@if(!empty($errors['cover']))
    <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-destructive">{{ $errors['cover'] }}</native:text>
@endif
