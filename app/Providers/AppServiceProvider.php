<?php

namespace App\Providers;

use DOMXPath;
use DOMDocument;
use Carbon\Carbon;
use App\Models\NewsArticle;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Thiết lập chiều dài mặc định của chuỗi
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        // // Lấy dữ liệu từ RSS feed
        // $rssFeedUrl = 'https://vnexpress.net/rss/thoi-su.rss';
        // try {
        //     $rssContent = simplexml_load_file($rssFeedUrl);

        //     // Kiểm tra xem có dữ liệu trong feed hay không
        //     if ($rssContent && isset($rssContent->channel->item)) {
        //         foreach ($rssContent->channel->item as $item) {
        //             // Lấy thông tin từ item
        //             $title = (string) $item->title;
        //             $link = (string) $item->link;
        //             $pubDate = date('Y-m-d H:i:s', strtotime((string) $item->pubDate));
        //             $image = (string) $item->enclosure['url'] ?? null;

        //             // Tải nội dung chi tiết từ link
        //             $detailHtml = file_get_contents($link); // Lấy nội dung HTML của bài viết
        //             $detailContent = ''; // Khởi tạo biến để lưu nội dung

        //             // Phân tích nội dung HTML để lấy thông tin chi tiết
        //             $dom = new DOMDocument();
        //             @$dom->loadHTML($detailHtml);
        //             $xpath = new DOMXPath($dom);
        //             $detailContentNode = $xpath->query('//div[@class="chi_tiet"]'); // Thay đổi đường dẫn cho phù hợp

        //             if ($detailContentNode->length > 0) {
        //                 $detailContent = $dom->saveHTML($detailContentNode->item(0)); // Lấy nội dung chi tiết
        //             }

        //             // Lưu bài viết vào cơ sở dữ liệu
        //             NewsArticle::create([
        //                 'title' => $title,
        //                 'content' => $detailContent, // Lưu nội dung chi tiết
        //                 'category' => "Thời sự", // Cập nhật theo ngữ cảnh
        //                 'author' => 'Tác giả chưa xác định', // Cập nhật nếu có thông tin
        //                 'image' => $image,
        //                 'is_published' => true,
        //                 'published_at' => $pubDate,
        //                 'slug' => Str::slug($title), // Tạo slug từ tiêu đề
        //             ]);
        //         }
        //         echo "Đã lưu tất cả bài viết.";
        //     } else {
        //         echo "Không có dữ liệu trong RSS feed.";
        //     }
        // } catch (Exception $e) {
        //     // Xử lý lỗi khi lấy dữ liệu từ RSS
        //     \Log::error('Error loading RSS feed: ' . $e->getMessage());
        // }
       

        // View::share('notifications', $notifications);
    }
}
