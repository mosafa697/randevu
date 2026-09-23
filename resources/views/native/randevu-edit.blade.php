<native:column class="w-full h-full p-4 gap-4 bg-theme-background">
    <native:row class="w-full">
        <native:button label="Back" @navigate.back />
    </native:row>

    <native:outlined-text-input label="Title" native:model="title" />
    @if(!empty($errors['title']))
        <native:text class="text-sm text-theme-destructive">{{ $errors['title'] }}</native:text>
    @endif

    <native:row class="w-full gap-2">
        <native:select label="Day" :options="$dayOptions" native:model="day" class="flex-1" />
        <native:select label="Month" :options="$monthOptions" native:model="month" class="flex-1" />
        <native:select label="Year" :options="$yearOptions" native:model="year" class="flex-1" />
    </native:row>
    @if(!empty($errors['occurs_on']))
        <native:text class="text-sm text-theme-destructive">{{ $errors['occurs_on'] }}</native:text>
    @endif

    <native:outlined-text-input label="Note (optional)" native:model="note" multiline :min-lines="2" />
    @if(!empty($errors['note']))
        <native:text class="text-sm text-theme-destructive">{{ $errors['note'] }}</native:text>
    @endif

    <native:button label="Save changes" @press="update" />

    @if(!$confirmingDelete)
        <native:button label="Delete" @press="askDelete" />
    @else
        <native:text class="text-base font-semibold text-theme-on-background">Delete this randevu?</native:text>
        <native:row class="w-full gap-2">
            <native:button label="Keep" @press="cancelDelete" />
            <native:button label="Delete" @press="destroy" />
        </native:row>
    @endif
</native:column>
