@php($rtl = $rtl ?? \App\Services\AppLocale::isRtl())
<native:row class="w-full gap-2 flex-wrap">
    @if($rtl)
        @if($show_hours)
            <native:chip :label="__('randevu.period_hours')" :selected="$show_hours" ios="checkmark" android="check" @change="toggleHours" class="min-h-12" />
        @else
            <native:chip :label="__('randevu.period_hours')" :selected="$show_hours" @change="toggleHours" class="min-h-12" />
        @endif
        @if($show_days)
            <native:chip :label="__('randevu.period_days')" :selected="$show_days" ios="checkmark" android="check" @change="toggleDays" class="min-h-12" />
        @else
            <native:chip :label="__('randevu.period_days')" :selected="$show_days" @change="toggleDays" class="min-h-12" />
        @endif
        @if($show_months)
            <native:chip :label="__('randevu.period_months')" :selected="$show_months" ios="checkmark" android="check" @change="toggleMonths" class="min-h-12" />
        @else
            <native:chip :label="__('randevu.period_months')" :selected="$show_months" @change="toggleMonths" class="min-h-12" />
        @endif
        @if($show_years)
            <native:chip :label="__('randevu.period_years')" :selected="$show_years" ios="checkmark" android="check" @change="toggleYears" class="min-h-12" />
        @else
            <native:chip :label="__('randevu.period_years')" :selected="$show_years" @change="toggleYears" class="min-h-12" />
        @endif
    @else
        @if($show_years)
            <native:chip :label="__('randevu.period_years')" :selected="$show_years" ios="checkmark" android="check" @change="toggleYears" class="min-h-12" />
        @else
            <native:chip :label="__('randevu.period_years')" :selected="$show_years" @change="toggleYears" class="min-h-12" />
        @endif
        @if($show_months)
            <native:chip :label="__('randevu.period_months')" :selected="$show_months" ios="checkmark" android="check" @change="toggleMonths" class="min-h-12" />
        @else
            <native:chip :label="__('randevu.period_months')" :selected="$show_months" @change="toggleMonths" class="min-h-12" />
        @endif
        @if($show_days)
            <native:chip :label="__('randevu.period_days')" :selected="$show_days" ios="checkmark" android="check" @change="toggleDays" class="min-h-12" />
        @else
            <native:chip :label="__('randevu.period_days')" :selected="$show_days" @change="toggleDays" class="min-h-12" />
        @endif
        @if($show_hours)
            <native:chip :label="__('randevu.period_hours')" :selected="$show_hours" ios="checkmark" android="check" @change="toggleHours" class="min-h-12" />
        @else
            <native:chip :label="__('randevu.period_hours')" :selected="$show_hours" @change="toggleHours" class="min-h-12" />
        @endif
    @endif
</native:row>
@if(!empty($errors['period_units']))
    <native:text class="text-sm text-theme-destructive">{{ $errors['period_units'] }}</native:text>
@endif
