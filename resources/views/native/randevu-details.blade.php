<native:column class="w-full h-full bg-theme-background">
    @if($randevu === null)
        <native:column class="w-full h-full p-5 bg-theme-background">
            <native:text class="{{ \App\Services\AppFonts::textClass('base') }} text-theme-on-surface-variant">{{ __('randevu.details_missing') }}</native:text>
        </native:column>
    @elseif($show_full_cover && !empty($randevu['cover']))
        <native:column class="w-full h-full bg-theme-background">
            <native:scroll-view class="w-full h-full">
                <native:column class="w-full p-4 gap-4 items-center">
                    <native:image :src="$randevu['cover']" :alt="__('randevu.cover_alt')" class="w-full rounded-2xl" :height="560" :fit="1" />
                    <native:button :label="__('randevu.cover_close')" ios-icon="xmark" android-icon="close" @press="closeCover" class="w-full min-h-12" />
                </native:column>
            </native:scroll-view>
        </native:column>
    @else
        <native:scroll-view class="w-full flex-1">
            <native:column class="w-full px-5 py-3 gap-4">
                <native:column class="w-full p-4 gap-3 bg-theme-surface rounded-2xl border border-theme-outline-variant">
                    <native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('base') }} text-theme-on-surface">{{ __('randevu.section_date_time') }}</native:text>
                    <native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('2xl') }} font-bold text-theme-on-surface">{{ $randevu['phrase'] }}</native:text>
                    <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface-variant">{{ __('randevu.exact_label') }}: {{ $randevu['exact'] }}</native:text>
                    <native:divider class="w-full" />
                    <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface">{{ __('randevu.gregorian_label') }}: {{ $randevu['absolute'] }}</native:text>
                    @if(!empty($randevu['hijri']))
                        <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface">{{ __('randevu.hijri_label') }}: {{ $randevu['hijri'] }} {{ \App\Services\RandevuHijri::hijriSuffix() }}</native:text>
                    @endif
                    <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface-variant">
                        {{ $randevu['is_appointment'] ? __('randevu.kind_appointment') : __('randevu.kind_memory') }}
                    </native:text>
                </native:column>

                <native:column class="w-full p-4 gap-3 bg-theme-surface rounded-2xl border border-theme-outline-variant">
                    <native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('base') }} text-theme-on-surface">{{ __('randevu.section_display_note') }}</native:text>
                    <native:row class="w-full items-center justify-between">
                        <native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('lg') }} font-semibold text-theme-on-surface">{{ $randevu['title'] }}</native:text>
                        @if($randevu['is_today'])
                            <native:badge :label="__('randevu.today')" variant="accent" />
                        @endif
                    </native:row>
                    @if(!empty($randevu['note']))
                        <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface-variant">{{ $randevu['note'] }}</native:text>
                    @endif
                </native:column>

                <native:column class="w-full p-4 gap-3 bg-theme-surface rounded-2xl border border-theme-outline-variant">
                    <native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('base') }} text-theme-on-surface">{{ __('randevu.section_appearance') }}</native:text>
                    @if(!empty($randevu['cover']))
                        <native:pressable @press="openCover" :a11y-label="__('randevu.cover_open')" class="w-full">
                            <native:image :src="$randevu['cover']" class="w-full rounded-xl" :height="200" :fit="2" />
                        </native:pressable>
                    @endif
                    @php($detailsAccent = isset($randevu['color']) && is_string($randevu['color']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $randevu['color']) ? $randevu['color'] : null)
                    @if($detailsAccent !== null)
                        <native:row class="w-full items-center">
                            <native:column :bg="$detailsAccent" class="w-8 h-2 rounded-full" />
                        </native:row>
                    @else
                        <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface-variant">{{ __('randevu.color_none') }}</native:text>
                    @endif
                </native:column>

                <native:column class="w-full p-4 gap-3 bg-theme-surface rounded-2xl border border-theme-outline-variant">
                    <native:text font="heading" class="{{ \App\Services\AppFonts::headingClass() }} {{ \App\Services\AppFonts::textClass('base') }} text-theme-on-surface">{{ __('randevu.section_notifications') }}</native:text>
                    <native:accordion>
                        <native:accordion-header>
                            <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} font-semibold text-theme-on-surface">{{ __('randevu.notification_settings') }}</native:text>
                        </native:accordion-header>
                        <native:accordion-content>
                            <native:text class="{{ \App\Services\AppFonts::textClass('sm') }} text-theme-on-surface-variant">{{ $randevu['notification_description'] }}</native:text>
                        </native:accordion-content>
                    </native:accordion>
                </native:column>

                <native:button :label="__('randevu.edit_cta')" ios-icon="pencil" android-icon="edit" @navigate="'/edit/'.$randevu['id']" class="w-full min-h-12" />
                <native:button :label="__('randevu.share_cta')" ios-icon="square.and.arrow.up" android-icon="share" @press="shareRandevu" variant="ghost" class="w-full min-h-12" />
                <native:button :label="__('randevu.duplicate_cta')" ios-icon="doc.on.doc" android-icon="content_copy" @press="duplicateNextYear" variant="ghost" class="w-full min-h-12" />
                <native:column class="w-full h-6" />
            </native:column>
        </native:scroll-view>
    @endif
</native:column>
