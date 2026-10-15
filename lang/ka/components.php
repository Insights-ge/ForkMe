<?php

return [
    'layout' => [
        'components' => 'კომპონენტები',
        'create_component' => 'კომპონენტის შექმნა',
        'edit_component' => 'კომპონენტის რედაქტირება',
    ],
    'table' => [
        'type' => 'ტიპი',
        'code' => 'კოდი',
        'description' => 'აღწერა',
        'color' => 'ფერი',
        'unique_code' => 'არტიკული',
        'empty_heading' => 'კომპონენტები ვერ მოიძებნა',
        'empty_description' => 'კომპონენტები იტვირთება პროდუქტის იმპორტიდან.',
        'delete_in_use_title' => 'კომპონენტი გამოიყენება',
        'delete_in_use_body' => 'ეს კომპონენტი გამოიყენება ერთ ან მეტ კარის ვარიანტში და მისი წაშლა შეუძლებელია.',
    ],
    'form' => [
        'type' => 'ტიპი',
        'code' => 'კოდი',
        'description' => 'აღწერა',
        'color' => 'ფერი',
        'unique_code' => 'არტიკული',
    ],
    'import' => [
        'errors' => [
            'file_unreadable' => 'ფაილის წაკითხვა ვერ მოხერხდა. ატვირთეთ სწორი .xlsx ფაილი.',
            'no_header' => 'ფაილს არ აქვს სათაურების მწკრივი.',
            'missing_column' => 'სვეტი :column ფაილში არ არის.',
            'unknown_column' => 'სვეტი :column უცნობია.',
            'duplicate_column' => 'სვეტი :column ერთზე მეტჯერ გვხვდება.',
            'too_many_rows' => 'ფაილში :max-ზე მეტი მწკრივია.',
        ],
    ],
];
