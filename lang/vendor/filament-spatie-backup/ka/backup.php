<?php

return [

    'components' => [
        'backup_destination_list' => [
            'table' => [
                'actions' => [
                    'download' => 'ჩამოტვირთვა',
                    'delete' => 'წაშლა',
                ],

                'fields' => [
                    'path' => 'გზა',
                    'disk' => 'დისკი',
                    'date' => 'თარიღი',
                    'size' => 'ზომა',
                ],

                'filters' => [
                    'disk' => 'დისკი',
                ],
            ],
        ],

        'backup_destination_status_list' => [
            'table' => [
                'fields' => [
                    'name' => 'სახელი',
                    'disk' => 'დისკი',
                    'healthy' => 'გამართული',
                    'amount' => 'რაოდენობა',
                    'newest' => 'უახლესი',
                    'used_storage' => 'გამოყენებული მეხსიერება',
                    'no_backups_present' => 'სარეზერვო ასლები არ არის',
                ],
            ],
        ],
    ],

    'pages' => [
        'backups' => [
            'actions' => [
                'create_backup' => 'სარეზერვო ასლის შექმნა',
            ],

            'heading' => 'სარეზერვო ასლები',

            'messages' => [
                'backup_success' => 'ახალი სარეზერვო ასლი ფონურ რეჟიმში იქმნება.',
                'backup_delete_success' => 'ეს სარეზერვო ასლი ფონურ რეჟიმში იშლება.',
            ],

            'modal' => [
                'buttons' => [
                    'only_db' => 'მხოლოდ ბაზა',
                    'only_files' => 'მხოლოდ ფაილები',
                    'db_and_files' => 'ბაზა და ფაილები',
                ],

                'label' => 'აირჩიეთ ვარიანტი',
            ],

            'navigation' => [
                'group' => 'პარამეტრები',
                'label' => 'სარეზერვო ასლები',
            ],
        ],
    ],

];
