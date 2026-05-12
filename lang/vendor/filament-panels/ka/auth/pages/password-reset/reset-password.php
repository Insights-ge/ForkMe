<?php

return [

    'title' => 'პაროლის აღდგენა',

    'heading' => 'პაროლის აღდგენა',

    'form' => [

        'email' => [
            'label' => 'ელ. ფოსტა',
        ],

        'password' => [
            'label' => 'პაროლი',
            'validation_attribute' => 'პაროლი',
        ],

        'password_confirmation' => [
            'label' => 'დაადასტურეთ პაროლი',
        ],

        'actions' => [

            'reset' => [
                'label' => 'პაროლის აღდგენა',
            ],

        ],

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'ზედმეტად ბევრი აღდგენის მცდელობა',
            'body' => 'გთხოვთ, სცადეთ ხელახლა :seconds წამში.',
        ],

    ],

];
