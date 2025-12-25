<?php
return [
    'modules' => [
        [
            'title' => 'Quản lí thành viên',
            'icon' => 'fa fa-user',
            'name' => 'users',

            'subModule' => [
                [
                    'title' => 'Quản lí thành viên',
                    'route' => '\admin\quantri'
                ],
                [
                    'title' => 'Quản lí nhóm thành viên',
                    // 'route'=> '\admin\catelogue'
                ],
                [
                    'title' => 'Quản lí nguoi dùng',
                    'route' => '\admin\users'
                ],
                [
                    'title' => 'Quản lí nhóm người dùng',
                    'route' => '\admin\catelogue'
                ],

            ]
        ],
        [
            'title' => 'Quản lí bài viết',
            'icon' => 'fa fa-blog',
            'name' => 'post',
            'subModule' => [
                [
                    'title' => 'Danh mục bài viết',
                    'route' => '\admin\postcategory'
                ],
                [
                    'title' => 'Quản lí bài viết',
                    'route' => '\admin\post'
                ],
                [
                    'title' => 'Quản lí trang đơn',
                    'route' => '\admin\singlepage'
                ],

            ]
        ],
        [
            'title' => 'Quản lí sản phẩm',
            'icon' => 'fa fa-blog',
            'name' => 'product',
            'subModule' => [
                [
                    'title' => 'Quản lí chất liệu',
                    'route' => '\admin\material'
                ],
                [
                    'title' => 'Danh mục sản phẩm',
                    'route' => '\admin\category'
                ],
                [
                    'title' => 'Quản lí sản phẩm',
                    'route' => '\admin\product'
                ],

            ]
        ],
        [
            'title' => 'Quản lí hệ thống',
            'icon' => 'fa fa-blog',
            'name' => 'system',
            'subModule' => [
                [
                    'title' => 'Cấu hinh chung',
                    'route' => '\admin\system'
                ],
                [
                    'title' => 'Cấu hinh sản phẩm',
                    'route' => '\admin\system'
                ],
            ]
        ]
    ],
];
