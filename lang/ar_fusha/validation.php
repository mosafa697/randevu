<?php

return [
    'required' => 'حقل :attribute مطلوب.',
    'date' => 'حقل :attribute يجب أن يكون تاريخًا صحيحًا.',
    'string' => 'حقل :attribute يجب أن يكون نصًا.',
    'integer' => 'حقل :attribute يجب أن يكون رقمًا.',
    'min' => [
        'numeric' => 'حقل :attribute يجب ألا يقل عن :min.',
        'string' => 'حقل :attribute يجب ألا يقل عن :min أحرف.',
    ],
    'max' => [
        'numeric' => 'حقل :attribute يجب ألا يزيد عن :max.',
        'string' => 'حقل :attribute يجب ألا يزيد عن :max حرفًا.',
    ],
    'in' => 'القيمة المحددة في :attribute غير صالحة.',
    'regex' => 'صيغة القيمة في :attribute غير صالحة.',

    'attributes' => [
        'title' => 'العنوان',
        'occurs_on' => 'التاريخ',
        'note' => 'الملاحظة',
        'entered_in' => 'التقويم',
        'color' => 'اللون',
    ],
];
