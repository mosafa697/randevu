<native:column class="w-full h-full p-4 gap-4 bg-theme-background">
    <native:row class="w-full items-center justify-between">
        <native:button label="Back" @navigate.back />
        <native:text class="text-xl font-bold text-theme-on-background">New randevu</native:text>
        <native:text class="text-sm text-theme-on-background"> </native:text>
    </native:row>

    <native:outlined-text-input label="Title" placeholder="Dentist, birthday…" native:model="title" />
    @if(!empty($errors['title']))
        <native:text class="text-sm text-theme-destructive">{{ $errors['title'] }}</native:text>
    @endif

    <native:outlined-text-input label="Date (YYYY-MM-DD)" placeholder="2026-10-01" native:model="occurs_on" keyboard="number" />
    @if(!empty($errors['occurs_on']))
        <native:text class="text-sm text-theme-destructive">{{ $errors['occurs_on'] }}</native:text>
    @endif

    <native:outlined-text-input label="Note (optional)" placeholder="Where, with whom…" native:model="note" multiline :min-lines="2" />
    @if(!empty($errors['note']))
        <native:text class="text-sm text-theme-destructive">{{ $errors['note'] }}</native:text>
    @endif

    <native:text class="text-sm text-theme-on-surface-variant">Today or later becomes an appointment; earlier becomes a memory.</native:text>

    <native:button label="Save randevu" @press="save" />
</native:column>
