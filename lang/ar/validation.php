<?php

return [
    'required' => 'خانة :attribute مطلوبة.',
    'date' => 'خانة :attribute لازم تبقى تاريخ سليم.',
    'string' => 'خانة :attribute لازم تبقى نص.',
    'integer' => 'خانة :attribute لازم تبقى رقم.',
    'min' => [
        'numeric' => 'خانة :attribute لازم تبقى :min على الأقل.',
        'string' => 'خانة :attribute لازم تبقى :min حروف على الأقل.',
    ],
    'max' => [
        'numeric' => 'خانة :attribute ماينفعش تزيد عن :max.',
        'string' => 'خانة :attribute ماينفعش تزيد عن :max حرف.',
    ],
    'in' => 'القيمة المختارة في :attribute مش سليمة.',

    'attributes' => [
        'title' => 'العنوان',
        'occurs_on' => 'التاريخ',
        'note' => 'الملاحظة',
        'entered_in' => 'التقويم',
    ],
];
