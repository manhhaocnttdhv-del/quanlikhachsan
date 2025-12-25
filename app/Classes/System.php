<?php
namespace App\Classes;

class System
{
    public function config()
    {
        $data['homepage'] = [
            'label' => 'Thông tin chung',
            'description' => "Cài đặt đầy đủ thông tin của website.Tên thương hiệu website, logo, Favicon, ....",
            'value' => [
                'company' => ['type' => 'text', 'label' => 'Tên công ty'],
                'brand' => ['type' => 'text', 'label' => 'Tên Thương hiệu'],
                'slogan' => ['type' => 'text', 'label' => 'Slogan'],
                'logo' => ['type' => 'file', 'label' => 'Logo website'],
                'favicon' => ['type' => 'file', 'label' => 'favicon'],
                'compyright' => ['type' => 'text', 'label' => 'Copyright'],
                // 'website' => [
                //     'type' => 'select',
                //     'label' => 'Tình trạng website',
                //     'option' =>
                //         [
                //             'open' => 'Mở cửa website',
                //             'close' => 'Website đang bảo trì'
                //         ]
                // ]
            ]
        ];
        $data['contact'] = [
            'label' => 'Thông tin chung',
            'description' => "Cài đặt đầy đủ thông tin liên hệ của website: Địa chỉ công ty, số điện thoai .v.v.v.v.v.v..v.v.v.v.v",
            'value' => [
                'office' => ['type' => 'text', 'label' => 'Địa chỉ công ty'],
                'address' => ['type' => 'text', 'label' => 'Văn phòng giao dịch'],
                'hotline' => ['type' => 'text', 'label' => 'Hotline'],
                'technical_phone' => ['type' => 'text', 'label' => 'Hotline kĩ thuật'],
                'sell_phone' => ['type' => 'text', 'label' => 'Hotline kinh doanh'],
                'phone' => ['type' => 'text', 'label' => 'Số cố định'],
                'Fax' => ['type' => 'text', 'label' => 'Fax'],
                'Email' => ['type' => 'text', 'label' => 'Email'],
                'tax' => ['type' => 'text', 'label' => 'tax'],
                'website' => ['type' => 'text', 'label' => 'website'],
                'map' => ['type' => 'textarea', 'label' => 'Bản đồ'],
            ]
        ];
        $data['seo'] = [
            'label' => 'Cấu hình seo dành cho trang chủ',
            'description' => "Cài đặt đầy đủ thông tin về Seo của trang chủ website:Bao gồm tiêu đề SEO, Từ khóa Seo",
            'value' => [
                'meta_title' => ['type' => 'text', 'label' => 'Tiêu đề SEO'],
                'meta_keyword' => ['type' => 'text', 'label' => 'Từ khóa SEO'],
                'meta_description' => ['type' => 'text', 'label' => 'Mô tả SEO'],
                'meta_image' => ['type' => 'file', 'label' => 'Hình ảnh SEO'],

            ]
        ];
        return $data;
    }
}