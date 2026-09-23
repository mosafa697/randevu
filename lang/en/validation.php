<?php

return [
    'required' => 'The :attribute field is required.',
    'date' => 'The :attribute field must be a valid date.',
    'string' => 'The :attribute field must be text.',
    'integer' => 'The :attribute field must be a number.',
    'min' => [
        'numeric' => 'The :attribute field must be at least :min.',
        'string' => 'The :attribute field must be at least :min characters.',
    ],
    'max' => [
        'numeric' => 'The :attribute field must not be greater than :max.',
        'string' => 'The :attribute field must not be greater than :max characters.',
    ],
    'in' => 'The selected :attribute is invalid.',

    'attributes' => [
        'title' => 'title',
        'occurs_on' => 'date',
        'note' => 'note',
        'entered_in' => 'calendar',
    ],
];
