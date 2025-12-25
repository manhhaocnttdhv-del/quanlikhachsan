<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Imports\RoomsImport;
use App\Exports\RoomsTemplateExport;
use App\Exports\ScrapedRoomsExport;
use Maatwebsite\Excel\Facades\Excel;
use DOMDocument;
use DOMXPath;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $query = Room::query();
        
        // Filter by status if provided
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        $rooms = $query->latest()->paginate(15);
        
        // Pass status filter to view for maintaining filter state
        $statusFilter = $request->get('status', '');
        
        return view('admin.rooms.index', compact('rooms', 'statusFilter'));
    }

    public function create()
    {
        return view('admin.rooms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|unique:rooms',
            'room_type' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'price_per_night' => 'required|numeric|min:300000',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'image' => 'nullable|image|max:2048', // Giữ lại cho ảnh chính
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
            'status' => 'required|in:available,occupied,maintenance',
        ], [
            'price_per_night.min' => 'Giá phòng tối thiểu là 300,000 VNĐ/đêm.',
        ]);

        // Xử lý ảnh chính (giữ lại để tương thích)
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('rooms', 'public');
        }

        $room = Room::create($validated);

        // Xử lý upload nhiều ảnh
        if ($request->hasFile('images')) {
            $order = 0;
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('rooms', 'public');
                RoomImage::create([
                    'room_id' => $room->id,
                    'image_path' => $imagePath,
                    'order' => $order++,
                    'is_primary' => $order === 1, // Ảnh đầu tiên là ảnh chính
                ]);
            }
        }

        // Nếu có ảnh chính từ field 'image', thêm vào room_images
        if ($request->hasFile('image')) {
            RoomImage::create([
                'room_id' => $room->id,
                'image_path' => $validated['image'],
                'order' => 0,
                'is_primary' => true,
            ]);
        }

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Thêm phòng thành công!');
    }

    public function show($id)
    {
        $room = Room::with(['bookings', 'images'])->findOrFail($id);
        return view('admin.rooms.show', compact('room'));
    }

    public function edit($id)
    {
        $room = Room::with('images')->findOrFail($id);
        return view('admin.rooms.edit', compact('room'));
    }

    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $validated = $request->validate([
            'room_number' => 'required|unique:rooms,room_number,' . $id,
            'room_type' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'price_per_night' => 'required|numeric|min:300000',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'image' => 'nullable|image|max:2048', // Giữ lại cho ảnh chính
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
            'status' => 'required|in:available,occupied,maintenance',
        ], [
            'price_per_night.min' => 'Giá phòng tối thiểu là 300,000 VNĐ/đêm.',
        ]);

        // Xử lý ảnh chính (giữ lại để tương thích)
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu có
            if ($room->image) {
                Storage::disk('public')->delete($room->image);
            }
            $validated['image'] = $request->file('image')->store('rooms', 'public');
        }

        $room->update($validated);

        // Xử lý upload nhiều ảnh mới
        if ($request->hasFile('images')) {
            $maxOrder = $room->images()->max('order') ?? -1;
            $order = $maxOrder + 1;
            
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('rooms', 'public');
                RoomImage::create([
                    'room_id' => $room->id,
                    'image_path' => $imagePath,
                    'order' => $order++,
                    'is_primary' => false,
                ]);
            }
        }

        // Nếu có ảnh chính từ field 'image', cập nhật hoặc tạo mới
        if ($request->hasFile('image')) {
            $primaryImage = $room->primaryImage;
            if ($primaryImage) {
                // Xóa ảnh cũ
                Storage::disk('public')->delete($primaryImage->image_path);
                $primaryImage->update([
                    'image_path' => $validated['image'],
                ]);
            } else {
                RoomImage::create([
                    'room_id' => $room->id,
                    'image_path' => $validated['image'],
                    'order' => 0,
                    'is_primary' => true,
                ]);
            }
        }

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Cập nhật phòng thành công!');
    }

    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        
        // Xóa tất cả ảnh
        foreach ($room->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }
        
        // Xóa ảnh chính nếu có
        if ($room->image) {
            Storage::disk('public')->delete($room->image);
        }
        
        $room->delete();

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Xóa phòng thành công!');
    }

    public function deleteImage($id)
    {
        $image = RoomImage::findOrFail($id);
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('success', 'Xóa ảnh thành công!');
    }

    public function setPrimaryImage($id)
    {
        $image = RoomImage::findOrFail($id);
        
        // Bỏ primary của tất cả ảnh khác
        RoomImage::where('room_id', $image->room_id)
            ->update(['is_primary' => false]);
        
        // Đặt ảnh này làm primary
        $image->update(['is_primary' => true]);
        
        // Cập nhật ảnh chính trong bảng rooms
        $image->room->update(['image' => $image->image_path]);

        return back()->with('success', 'Đặt ảnh chính thành công!');
    }

    public function showScrapeForm()
    {
        return view('admin.rooms.scrape');
    }

    public function showImportForm()
    {
        return view('admin.rooms.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('file');
            
            // Import rooms from Excel/CSV
            Excel::import(new RoomsImport, $file);
            
            return redirect()->route('admin.rooms.index')
                ->with('success', 'Import phòng từ file Excel/CSV thành công!');
                
        } catch (\Exception $e) {
            Log::error("Import rooms error", ['error' => $e->getMessage()]);
            return back()->withErrors(['file' => 'Lỗi khi import: ' . $e->getMessage()])->withInput();
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new RoomsTemplateExport(), 'template_import_phong.xlsx');
    }

    public function scrapeAndExport(Request $request)
    {
        $request->validate([
            'booking_url' => 'required|url',
        ]);

        $url = $request->input('booking_url');
        
        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return back()->withErrors(['booking_url' => 'URL không hợp lệ.'])->withInput();
        }
        
        // Detect website type
        $websiteType = $this->detectWebsiteType($url);
        
        if (!$websiteType) {
            return back()->withErrors(['booking_url' => 'URL không hợp lệ. Hệ thống hỗ trợ: Booking.com, Agoda.com, Expedia, Hotels.com, Traveloka, VnTravel, Mytour.vn, Luxstay.'])->withInput();
        }
        
        try {
            // Get website-specific headers
            $headers = $this->getWebsiteHeaders($websiteType, $url);
            
            // Fetch the page with proper headers
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->retry(2, 100)
                ->get($url);

            if (!$response->successful()) {
                $siteName = $this->getWebsiteName($websiteType);
                $statusCode = $response->status();
                $errorMsg = "Không thể tải trang {$siteName}.";
                
                if ($statusCode === 403) {
                    $errorMsg .= " Trang web đang chặn bot/scraper (403 Forbidden).";
                } elseif ($statusCode === 404) {
                    $errorMsg .= " URL không tồn tại.";
                } else {
                    $errorMsg .= " Lỗi HTTP {$statusCode}.";
                }
                
                return back()->withErrors(['booking_url' => $errorMsg])->withInput();
            }

            $html = $response->body();
            
            // Parse HTML
            $dom = new DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
            $xpath = new DOMXPath($dom);

            // Extract rooms based on website type
            $rooms = $this->extractRoomsByWebsiteType($websiteType, $html, $xpath, $url);
            
            if (empty($rooms)) {
                $siteName = $this->getWebsiteName($websiteType);
                return back()->withErrors(['booking_url' => "Không tìm thấy thông tin phòng trên trang {$siteName} này. Vui lòng kiểm tra lại URL."])->withInput();
            }

            // Export to Excel
            $filename = 'rooms_scraped_' . date('Y-m-d_His') . '.xlsx';
            return Excel::download(new ScrapedRoomsExport($rooms), $filename);
                
        } catch (\Exception $e) {
            Log::error("Scrape and export error", [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['booking_url' => 'Lỗi khi scrape và export: ' . $e->getMessage()])->withInput();
        }
    }

    public function scrape(Request $request)
    {
        $request->validate([
            'booking_url' => 'required|url',
        ]);

        $url = $request->input('booking_url');
        
        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return back()->withErrors(['booking_url' => 'URL không hợp lệ. Vui lòng nhập URL đầy đủ (ví dụ: https://www.expedia.com/Hotel-Search?...).'])->withInput();
        }
        
        // Check if URL is complete (not just a placeholder like "?...")
        if (strpos($url, '?...') !== false || 
            (strpos($url, 'expedia.com') !== false && strpos($url, '?') === false && strpos($url, '/hotel/') === false && strpos($url, '/Hotel-') === false)) {
            return back()->withErrors(['booking_url' => 'URL không đầy đủ. Vui lòng copy URL đầy đủ từ trình duyệt (bao gồm cả các tham số sau dấu ? nếu có).'])->withInput();
        }
        
        // Detect website type
        $websiteType = $this->detectWebsiteType($url);
        
        if (!$websiteType) {
            return back()->withErrors(['booking_url' => 'URL không hợp lệ. Hệ thống hỗ trợ: Booking.com, Agoda.com, Expedia, Hotels.com, Traveloka, VnTravel, Mytour.vn, Luxstay.'])->withInput();
        }
        
        try {
            // Get website-specific headers
            $headers = $this->getWebsiteHeaders($websiteType, $url);
            
            // Fetch the page with proper headers
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->retry(2, 100) // Retry 2 times with 100ms delay
                ->get($url);

            if (!$response->successful()) {
                $siteName = $this->getWebsiteName($websiteType);
                $statusCode = $response->status();
                $errorMsg = "Không thể tải trang {$siteName}.";
                
                // Provide more specific error messages
                if ($statusCode === 403) {
                    $errorMsg .= " Trang web đang chặn bot/scraper (403 Forbidden).";
                    $errorMsg .= " Một số trang web như Expedia, Hotels.com có hệ thống bảo vệ chống bot rất mạnh.";
                    $errorMsg .= " Vui lòng thử sử dụng các trang web khác như Booking.com, Agoda.com, Traveloka, VnTravel, Mytour.vn hoặc Luxstay.";
                } elseif ($statusCode === 404) {
                    $errorMsg .= " URL không tồn tại. Vui lòng kiểm tra lại URL.";
                } elseif ($statusCode === 429) {
                    $errorMsg .= " Quá nhiều request. Vui lòng đợi vài phút rồi thử lại.";
                } else {
                    $errorMsg .= " Lỗi HTTP {$statusCode}. Vui lòng thử lại.";
                }
                
                Log::warning("Failed to fetch page", [
                    'url' => $url,
                    'website' => $websiteType,
                    'status' => $statusCode,
                    'response' => substr($response->body(), 0, 500)
                ]);
                
                return back()->withErrors(['booking_url' => $errorMsg])->withInput();
            }

            $html = $response->body();
            
            // Parse HTML
            $dom = new DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
            $xpath = new DOMXPath($dom);

            // Detect page type for better error messages
            $pageType = $this->detectPageType($url, $html);
            
            // Extract rooms based on website type
            $rooms = $this->extractRoomsByWebsiteType($websiteType, $html, $xpath, $url);
            
            if (empty($rooms)) {
                $siteName = $this->getWebsiteName($websiteType);
                $errorMsg = "Không tìm thấy thông tin phòng trên trang {$siteName} này.";
                
                if ($pageType === 'city') {
                    $errorMsg .= " URL này là trang thành phố. Vui lòng sử dụng URL trang chi tiết khách sạn hoặc trang tìm kiếm có kết quả phòng.";
                } elseif ($pageType === 'search') {
                    $errorMsg .= " Vui lòng kiểm tra lại URL hoặc thử URL trang chi tiết khách sạn.";
                } else {
                    $errorMsg .= " Vui lòng kiểm tra lại URL.";
                }
                
                return back()->withErrors(['booking_url' => $errorMsg])->withInput();
            }

            $savedCount = 0;
            $errors = [];

            foreach ($rooms as $roomData) {
                try {
                    // Generate unique room number if not provided
                    $prefix = $this->getWebsitePrefix($websiteType);
                    $roomNumber = $roomData['room_number'] ?? $prefix . uniqid();
                    
                    // Check if room number already exists
                    $existingRoom = Room::where('room_number', $roomNumber)->first();
                    if ($existingRoom) {
                        $roomNumber = $prefix . time() . '-' . rand(1000, 9999);
                    }

                    // Create room
                    $room = Room::create([
                        'room_number' => $roomNumber,
                        'room_type' => $roomData['room_type'] ?? 'Standard',
                        'capacity' => $roomData['capacity'] ?? 2,
                        'price_per_night' => $roomData['price_per_night'] ?? 0,
                        'description' => $roomData['description'] ?? '',
                        'amenities' => $roomData['amenities'] ?? [],
                        'status' => 'available',
                    ]);

                    // Download and save images
                    if (!empty($roomData['images'])) {
                        $order = 0;
                        foreach ($roomData['images'] as $imageUrl) {
                            try {
                                $imagePath = $this->downloadImage($imageUrl, $room->id);
                                if ($imagePath) {
                                    RoomImage::create([
                                        'room_id' => $room->id,
                                        'image_path' => $imagePath,
                                        'order' => $order++,
                                        'is_primary' => $order === 1,
                                    ]);
                                    
                                    // Set first image as primary
                                    if ($order === 1) {
                                        $room->update(['image' => $imagePath]);
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::warning("Failed to download image: {$imageUrl}", ['error' => $e->getMessage()]);
                            }
                        }
                    }

                    $savedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Lỗi khi lưu phòng: " . $e->getMessage();
                    Log::error("Failed to save room", ['error' => $e->getMessage(), 'data' => $roomData]);
                }
            }

            $message = "Đã lưu thành công {$savedCount} phòng vào database.";
            if (!empty($errors)) {
                $message .= " Có " . count($errors) . " lỗi xảy ra.";
            }

            return redirect()->route('admin.rooms.index')
                ->with('success', $message)
                ->with('errors', $errors);

        } catch (\Exception $e) {
            Log::error("Scraping error", ['error' => $e->getMessage(), 'url' => $url]);
            return back()->withErrors(['booking_url' => 'Lỗi khi scrape: ' . $e->getMessage()])->withInput();
        }
    }

    private function extractRoomsFromBooking($html, $xpath)
    {
        $rooms = [];

        // Method 1: Try to find JSON-LD structured data
        preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $jsonMatches);
        
        foreach ($jsonMatches[1] as $jsonStr) {
            $data = json_decode($jsonStr, true);
            if ($data) {
                // Handle array of JSON-LD objects
                if (isset($data[0])) {
                    foreach ($data as $item) {
                        if (isset($item['@type']) && $item['@type'] === 'Hotel') {
                            if (isset($item['containsPlace'])) {
                                foreach ($item['containsPlace'] as $place) {
                                    if (isset($place['@type']) && $place['@type'] === 'HotelRoom') {
                                        $rooms[] = $this->parseRoomFromJsonLd($place);
                                    }
                                }
                            }
                        } elseif (isset($item['@type']) && $item['@type'] === 'HotelRoom') {
                            $rooms[] = $this->parseRoomFromJsonLd($item);
                        }
                    }
                } elseif (isset($data['@type']) && $data['@type'] === 'Hotel') {
                    // Extract hotel room information
                    if (isset($data['containsPlace'])) {
                        foreach ($data['containsPlace'] as $place) {
                            if (isset($place['@type']) && $place['@type'] === 'HotelRoom') {
                                $rooms[] = $this->parseRoomFromJsonLd($place);
                            }
                        }
                    }
                } elseif (isset($data['@type']) && $data['@type'] === 'HotelRoom') {
                    $rooms[] = $this->parseRoomFromJsonLd($data);
                }
            }
        }

        // Method 2: Try to find Booking.com specific JSON data (for hotel detail pages)
        // First, try to find complete JSON objects in script tags
        preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $html, $scriptMatches);
        
        foreach ($scriptMatches[1] as $scriptContent) {
            // Skip if script is too small or doesn't contain relevant keywords
            if (strlen($scriptContent) < 100 || 
                (strpos($scriptContent, 'room') === false && 
                 strpos($scriptContent, 'Room') === false &&
                 strpos($scriptContent, 'property') === false)) {
                continue;
            }
            
            // Pattern 1: Try to find complete JSON objects with roomTypes
            // Look for patterns like: "roomTypes": [{...}, {...}]
            if (preg_match('/"roomTypes?"\s*:\s*\[(.*?)\]/is', $scriptContent, $roomMatches)) {
                $roomData = $this->extractJsonArray($roomMatches[1]);
                if ($roomData && count($roomData) > 0) {
                    foreach ($roomData as $room) {
                        if (is_array($room) && !empty($room)) {
                            $rooms[] = $this->parseBookingRoomFromJson($room);
                        }
                    }
                }
            }
            
            // Pattern 2: propertyRoomTypes
            if (preg_match('/"propertyRoomTypes?"\s*:\s*\[(.*?)\]/is', $scriptContent, $roomMatches)) {
                $roomData = $this->extractJsonArray($roomMatches[1]);
                if ($roomData && count($roomData) > 0) {
                    foreach ($roomData as $room) {
                        if (is_array($room) && !empty($room)) {
                            $rooms[] = $this->parseBookingRoomFromJson($room);
                        }
                    }
                }
            }
            
            // Pattern 3: rooms array
            if (preg_match('/"rooms?"\s*:\s*\[(.*?)\]/is', $scriptContent, $roomMatches)) {
                $roomData = $this->extractJsonArray($roomMatches[1]);
                if ($roomData && count($roomData) > 0) {
                    foreach ($roomData as $room) {
                        if (is_array($room) && !empty($room)) {
                            $rooms[] = $this->parseBookingRoomFromJson($room);
                        }
                    }
                }
            }
            
            // Pattern 4: window.b_hotel_data or similar global variables
            if (preg_match('/window\.(?:b_)?(?:hotel_)?data\s*=\s*({.*?});/is', $scriptContent, $hotelMatches)) {
                $hotelData = json_decode($hotelMatches[1], true);
                if ($hotelData) {
                    if (isset($hotelData['roomTypes']) && is_array($hotelData['roomTypes'])) {
                        foreach ($hotelData['roomTypes'] as $room) {
                            $rooms[] = $this->parseBookingRoomFromJson($room);
                        }
                    }
                    if (isset($hotelData['property']) && isset($hotelData['property']['roomTypes'])) {
                        foreach ($hotelData['property']['roomTypes'] as $room) {
                            $rooms[] = $this->parseBookingRoomFromJson($room);
                        }
                    }
                }
            }
            
            // Pattern 5: Look for room data in property object
            if (preg_match('/"property"\s*:\s*({[^}]*"roomTypes?"\s*:\s*\[.*?\].*?})/is', $scriptContent, $propertyMatches)) {
                $propertyData = json_decode($propertyMatches[1], true);
                if ($propertyData && isset($propertyData['roomTypes']) && is_array($propertyData['roomTypes'])) {
                    foreach ($propertyData['roomTypes'] as $room) {
                        $rooms[] = $this->parseBookingRoomFromJson($room);
                    }
                }
            }
            
            // Pattern 6: Try to find JSON-LD in script content (sometimes embedded)
            if (preg_match('/application\/ld\+json[^>]*>(.*?)<\/script>/is', $scriptContent, $jsonLdMatches)) {
                $jsonLdData = json_decode($jsonLdMatches[1], true);
                if ($jsonLdData && isset($jsonLdData['containsPlace'])) {
                    foreach ($jsonLdData['containsPlace'] as $place) {
                        if (isset($place['@type']) && $place['@type'] === 'HotelRoom') {
                            $rooms[] = $this->parseRoomFromJsonLd($place);
                        }
                    }
                }
            }
            
            // Pattern 7: Try to find room data in React/Next.js __NEXT_DATA__ or similar
            if (preg_match('/__NEXT_DATA__\s*=\s*({.*?});/is', $scriptContent, $nextMatches)) {
                $nextData = json_decode($nextMatches[1], true);
                if ($nextData) {
                    $this->extractRoomsFromNestedData($nextData, $rooms);
                }
            }
            
            // Pattern 8: Look for room data in any large JSON object
            if (preg_match('/\{[^{}]*"roomTypes?"\s*:\s*\[.*?\].*?\}/is', $scriptContent, $jsonMatches)) {
                $jsonData = json_decode($jsonMatches[0], true);
                if ($jsonData && isset($jsonData['roomTypes']) && is_array($jsonData['roomTypes'])) {
                    foreach ($jsonData['roomTypes'] as $room) {
                        $rooms[] = $this->parseBookingRoomFromJson($room);
                    }
                }
            }
        }

        // Method 3: Parse HTML structure for hotel detail page
        if (empty($rooms)) {
            $rooms = $this->parseRoomsFromBookingHTML($xpath);
        }

        return $rooms;
    }

    private function extractJsonArray($jsonStr)
    {
        // Try to extract a valid JSON array from string
        // This handles cases where the array might be incomplete or have nested structures
        $jsonStr = trim($jsonStr);
        if (empty($jsonStr)) {
            return null;
        }
        
        // Try to parse as complete array
        $data = json_decode('[' . $jsonStr . ']', true);
        if ($data !== null) {
            return $data;
        }
        
        // Try to find individual room objects
        preg_match_all('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/', $jsonStr, $matches);
        if (!empty($matches[0])) {
            $rooms = [];
            foreach ($matches[0] as $match) {
                $room = json_decode($match, true);
                if ($room) {
                    $rooms[] = $room;
                }
            }
            return $rooms;
        }
        
        return null;
    }

    private function parseBookingRoomFromJson($data)
    {
        return [
            'room_number' => $data['name'] ?? $data['roomName'] ?? $data['title'] ?? $data['roomTypeName'] ?? $data['room_type_name'] ?? null,
            'room_type' => $this->extractRoomType($data['name'] ?? $data['roomName'] ?? $data['type'] ?? $data['roomType'] ?? 'Standard'),
            'capacity' => $data['maxOccupancy'] ?? $data['occupancy'] ?? $data['capacity'] ?? $data['max_occupancy'] ?? $data['guestCount'] ?? 2,
            'price_per_night' => $this->extractBookingPrice($data),
            'description' => $data['description'] ?? $data['roomDescription'] ?? $data['room_description'] ?? '',
            'amenities' => $this->extractBookingAmenities($data),
            'images' => $this->extractBookingImages($data),
        ];
    }

    private function extractBookingPrice($data)
    {
        if (isset($data['price'])) {
            if (is_array($data['price'])) {
                return (float)($data['price']['amount'] ?? $data['price']['value'] ?? $data['price']['price'] ?? 0);
            }
            return (float)$data['price'];
        }
        if (isset($data['rate'])) {
            return (float)$data['rate'];
        }
        if (isset($data['pricePerNight'])) {
            return (float)$data['pricePerNight'];
        }
        if (isset($data['basePrice'])) {
            return (float)$data['basePrice'];
        }
        if (isset($data['minPrice'])) {
            return (float)$data['minPrice'];
        }
        return 0;
    }

    private function extractBookingAmenities($data)
    {
        $amenities = [];
        if (isset($data['amenities']) && is_array($data['amenities'])) {
            foreach ($data['amenities'] as $amenity) {
                if (is_string($amenity)) {
                    $amenities[] = $amenity;
                } elseif (isset($amenity['name'])) {
                    $amenities[] = $amenity['name'];
                } elseif (isset($amenity['title'])) {
                    $amenities[] = $amenity['title'];
                }
            }
        }
        if (isset($data['facilities']) && is_array($data['facilities'])) {
            foreach ($data['facilities'] as $facility) {
                if (is_string($facility)) {
                    $amenities[] = $facility;
                } elseif (isset($facility['name'])) {
                    $amenities[] = $facility['name'];
                }
            }
        }
        return array_unique($amenities);
    }

    private function extractBookingImages($data)
    {
        $images = [];
        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $img) {
                if (is_string($img)) {
                    $images[] = $img;
                } elseif (isset($img['url'])) {
                    $images[] = $img['url'];
                } elseif (isset($img['src'])) {
                    $images[] = $img['src'];
                } elseif (isset($img['imageUrl'])) {
                    $images[] = $img['imageUrl'];
                } elseif (isset($img['original'])) {
                    $images[] = $img['original'];
                }
            }
        }
        if (isset($data['image']) && is_string($data['image'])) {
            $images[] = $data['image'];
        }
        if (isset($data['thumbnail']) && is_string($data['thumbnail'])) {
            $images[] = $data['thumbnail'];
        }
        if (isset($data['photo'])) {
            if (is_string($data['photo'])) {
                $images[] = $data['photo'];
            } elseif (is_array($data['photo']) && isset($data['photo']['url'])) {
                $images[] = $data['photo']['url'];
            }
        }
        return array_filter($images);
    }

    private function extractRoomsFromNestedData($data, &$rooms, $depth = 0)
    {
        // Prevent infinite recursion
        if ($depth > 10) {
            return;
        }
        
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                // Check if this looks like room data
                if (is_array($value) && 
                    (isset($value['roomTypeName']) || 
                     isset($value['roomName']) || 
                     isset($value['name']) ||
                     (isset($value['roomTypes']) && is_array($value['roomTypes'])))) {
                    
                    if (isset($value['roomTypes']) && is_array($value['roomTypes'])) {
                        foreach ($value['roomTypes'] as $room) {
                            if (is_array($room) && !empty($room)) {
                                $rooms[] = $this->parseBookingRoomFromJson($room);
                            }
                        }
                    } elseif (isset($value['roomTypeName']) || isset($value['roomName']) || isset($value['name'])) {
                        $rooms[] = $this->parseBookingRoomFromJson($value);
                    }
                } elseif (is_array($value)) {
                    // Recursively search
                    $this->extractRoomsFromNestedData($value, $rooms, $depth + 1);
                }
            }
        }
    }

    private function parseRoomsFromBookingHTML($xpath)
    {
        $rooms = [];
        
        // Try to find Booking.com room elements (for hotel detail pages)
        // Booking.com uses various class names for room cards
        $selectors = [
            "//div[contains(@class, 'hprt-roomtype')]",
            "//div[contains(@class, 'hprt-roomtype-card')]",
            "//div[contains(@class, 'room-type')]",
            "//div[contains(@class, 'RoomType')]",
            "//div[contains(@data-testid, 'room')]",
            "//div[contains(@data-testid, 'Room')]",
            "//div[contains(@class, 'room') and contains(@class, 'card')]",
            "//div[contains(@class, 'Room') and contains(@class, 'Card')]",
            "//div[contains(@id, 'room')]",
            "//div[contains(@id, 'Room')]",
        ];
        
        $roomNodes = null;
        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes && $nodes->length > 0) {
                $roomNodes = $nodes;
                break;
            }
        }
        
        if (!$roomNodes || $roomNodes->length === 0) {
            // Try to find room cards in search results
            $roomNodes = $xpath->query("//div[contains(@class, 'property') or contains(@class, 'Property')]");
        }
        
        foreach ($roomNodes as $node) {
            $name = $xpath->evaluate("string(.//h2 | .//h3 | .//h4 | .//h5 | .//span[contains(@class, 'name')] | .//div[contains(@class, 'name')] | .//a[contains(@class, 'room-name')] | .//span[contains(@data-testid, 'room-name')])", $node);
            $price = $xpath->evaluate("string(.//span[contains(@class, 'price')] | .//div[contains(@class, 'price')] | .//span[contains(@class, 'Price')] | .//b[contains(@class, 'price')] | .//span[contains(@data-testid, 'price')])", $node);
            $description = $xpath->evaluate("string(.//p | .//div[contains(@class, 'description')] | .//div[contains(@class, 'room-description')] | .//div[contains(@class, 'hprt-roomtype-description')])", $node);
            
            if ($name && trim($name)) {
                $rooms[] = [
                    'room_number' => trim($name),
                    'room_type' => $this->extractRoomType($name),
                    'capacity' => $this->extractCapacityFromText($xpath->evaluate("string(.)", $node)),
                    'price_per_night' => $this->parsePrice($price),
                    'description' => trim($description),
                    'amenities' => [],
                    'images' => $this->extractImagesFromNode($xpath, $node),
                ];
            }
        }
        
        return $rooms;
    }

    private function extractRoomsFromAgoda($html, $xpath, $url = '')
    {
        $rooms = [];

        // Method 1: Try to find JSON-LD structured data
        preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $jsonMatches);
        
        foreach ($jsonMatches[1] as $jsonStr) {
            $data = json_decode($jsonStr, true);
            if ($data) {
                // Handle array of JSON-LD objects
                if (isset($data[0])) {
                    foreach ($data as $item) {
                        if (isset($item['@type']) && ($item['@type'] === 'Hotel' || $item['@type'] === 'HotelRoom')) {
                            if ($item['@type'] === 'HotelRoom') {
                                $rooms[] = $this->parseRoomFromJsonLd($item);
                            } elseif (isset($item['containsPlace'])) {
                                foreach ($item['containsPlace'] as $place) {
                                    if (isset($place['@type']) && $place['@type'] === 'HotelRoom') {
                                        $rooms[] = $this->parseRoomFromJsonLd($place);
                                    }
                                }
                            }
                        }
                    }
                } elseif (isset($data['@type']) && $data['@type'] === 'Hotel') {
                    if (isset($data['containsPlace'])) {
                        foreach ($data['containsPlace'] as $place) {
                            if (isset($place['@type']) && $place['@type'] === 'HotelRoom') {
                                $rooms[] = $this->parseRoomFromJsonLd($place);
                            }
                        }
                    }
                } elseif (isset($data['@type']) && $data['@type'] === 'HotelRoom') {
                    $rooms[] = $this->parseRoomFromJsonLd($data);
                }
            }
        }

        // Method 2: Try to find Agoda-specific JSON data
        // Agoda often uses window.__INITIAL_STATE__, window.__APOLLO_STATE__, or similar
        preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $html, $scriptMatches);
        
        foreach ($scriptMatches[1] as $scriptContent) {
            // Skip if script is too small or doesn't contain relevant keywords
            if (strlen($scriptContent) < 100 || 
                (strpos($scriptContent, 'room') === false && 
                 strpos($scriptContent, 'Room') === false &&
                 strpos($scriptContent, 'property') === false &&
                 strpos($scriptContent, 'Property') === false &&
                 strpos($scriptContent, 'apollo') === false &&
                 strpos($scriptContent, 'Apollo') === false)) {
                continue;
            }
            
            // Pattern 1: __INITIAL_STATE__ or window.__INITIAL_STATE__
            if (preg_match('/__INITIAL_STATE__\s*=\s*({.*?});/is', $scriptContent, $stateMatches)) {
                $stateData = json_decode($stateMatches[1], true);
                if ($stateData) {
                    if (isset($stateData['roomList']) || isset($stateData['rooms'])) {
                        $roomList = $stateData['roomList'] ?? $stateData['rooms'] ?? [];
                        foreach ($roomList as $room) {
                            if (is_array($room) && !empty($room)) {
                                $rooms[] = $this->parseAgodaRoomFromJson($room);
                            }
                        }
                    }
                    // Also check for nested room data
                    if (isset($stateData['property']) && isset($stateData['property']['roomTypes'])) {
                        foreach ($stateData['property']['roomTypes'] as $room) {
                            if (is_array($room) && !empty($room)) {
                                $rooms[] = $this->parseAgodaRoomFromJson($room);
                            }
                        }
                    }
                    // Recursively search in state data
                    $this->extractRoomsFromNestedData($stateData, $rooms);
                }
            }
            
            // Pattern 2: __APOLLO_STATE__ (Agoda uses Apollo GraphQL)
            if (preg_match('/__APOLLO_STATE__\s*=\s*({.*?});/is', $scriptContent, $apolloMatches)) {
                $apolloData = json_decode($apolloMatches[1], true);
                if ($apolloData) {
                    // Search for room data in Apollo state
                    $this->extractRoomsFromApolloState($apolloData, $rooms);
                }
            }
            
            // Pattern 3: Try to find complete JSON objects with roomTypes
            if (preg_match('/"roomTypes?"\s*:\s*\[(.*?)\]/is', $scriptContent, $roomMatches)) {
                $roomData = $this->extractJsonArray($roomMatches[1]);
                if ($roomData && count($roomData) > 0) {
                    foreach ($roomData as $room) {
                        if (is_array($room) && !empty($room)) {
                            $rooms[] = $this->parseAgodaRoomFromJson($room);
                        }
                    }
                }
            }
            
            // Pattern 4: propertyRoomTypes
            if (preg_match('/"propertyRoomTypes?"\s*:\s*\[(.*?)\]/is', $scriptContent, $roomMatches)) {
                $roomData = $this->extractJsonArray($roomMatches[1]);
                if ($roomData && count($roomData) > 0) {
                    foreach ($roomData as $room) {
                        if (is_array($room) && !empty($room)) {
                            $rooms[] = $this->parseAgodaRoomFromJson($room);
                        }
                    }
                }
            }
            
            // Pattern 5: Look for room data in property object
            if (preg_match('/"property"\s*:\s*({[^}]*"roomTypes?"\s*:\s*\[.*?\].*?})/is', $scriptContent, $propertyMatches)) {
                $propertyData = json_decode($propertyMatches[1], true);
                if ($propertyData && isset($propertyData['roomTypes']) && is_array($propertyData['roomTypes'])) {
                    foreach ($propertyData['roomTypes'] as $room) {
                        if (is_array($room) && !empty($room)) {
                            $rooms[] = $this->parseAgodaRoomFromJson($room);
                        }
                    }
                }
            }
            
            // Pattern 6: Look for hotelRoomTypes
            if (preg_match('/"hotelRoomTypes?"\s*:\s*\[(.*?)\]/is', $scriptContent, $roomMatches)) {
                $roomData = $this->extractJsonArray($roomMatches[1]);
                if ($roomData && count($roomData) > 0) {
                    foreach ($roomData as $room) {
                        if (is_array($room) && !empty($room)) {
                            $rooms[] = $this->parseAgodaRoomFromJson($room);
                        }
                    }
                }
            }
            
            // Pattern 7: Look for room data in search results
            if (preg_match('/"searchResults?"\s*:\s*\[(.*?)\]/is', $scriptContent, $searchMatches)) {
                $searchData = $this->extractJsonArray($searchMatches[1]);
                if ($searchData) {
                    foreach ($searchData as $result) {
                        if (isset($result['roomTypes']) && is_array($result['roomTypes'])) {
                            foreach ($result['roomTypes'] as $room) {
                                if (is_array($room) && !empty($room)) {
                                    $rooms[] = $this->parseAgodaRoomFromJson($room);
                                }
                            }
                        }
                    }
                }
            }
            
            // Pattern 8: Look for Agoda-specific GraphQL queries
            if (preg_match('/"data"\s*:\s*({.*?"roomTypes?"\s*:\s*\[.*?\].*?})/is', $scriptContent, $dataMatches)) {
                $graphqlData = json_decode($dataMatches[1], true);
                if ($graphqlData) {
                    $this->extractRoomsFromNestedData($graphqlData, $rooms);
                }
            }
            
            // Pattern 9: Look for room data in any large JSON object
            if (preg_match('/\{[^{}]*"roomTypes?"\s*:\s*\[.*?\].*?\}/is', $scriptContent, $jsonMatches)) {
                $jsonData = json_decode($jsonMatches[0], true);
                if ($jsonData && isset($jsonData['roomTypes']) && is_array($jsonData['roomTypes'])) {
                    foreach ($jsonData['roomTypes'] as $room) {
                        if (is_array($room) && !empty($room)) {
                            $rooms[] = $this->parseAgodaRoomFromJson($room);
                        }
                    }
                }
            }
            
            // Pattern 10: Look for Agoda cache data
            if (preg_match('/"cache"\s*:\s*({.*?})/is', $scriptContent, $cacheMatches)) {
                $cacheData = json_decode($cacheMatches[1], true);
                if ($cacheData) {
                    $this->extractRoomsFromNestedData($cacheData, $rooms);
                }
            }
        }

        // Method 3: Parse HTML structure for Agoda
        if (empty($rooms)) {
            $rooms = $this->parseRoomsFromAgodaHTML($xpath);
        }

        return $rooms;
    }

    private function extractRoomsFromApolloState($apolloData, &$rooms, $depth = 0)
    {
        // Prevent infinite recursion
        if ($depth > 15) {
            return;
        }
        
        // Apollo state is a complex nested structure
        // Search recursively for room-related data
        foreach ($apolloData as $key => $value) {
            if (is_array($value)) {
                // Check if this looks like room data
                if (isset($value['roomTypeName']) || 
                    isset($value['roomName']) || 
                    isset($value['name']) ||
                    isset($value['roomType']) ||
                    (isset($value['roomTypes']) && is_array($value['roomTypes']))) {
                    
                    if (isset($value['roomTypes']) && is_array($value['roomTypes'])) {
                        foreach ($value['roomTypes'] as $room) {
                            if (is_array($room) && !empty($room)) {
                                $rooms[] = $this->parseAgodaRoomFromJson($room);
                            }
                        }
                    } elseif (isset($value['roomTypeName']) || isset($value['roomName']) || isset($value['name'])) {
                        $rooms[] = $this->parseAgodaRoomFromJson($value);
                    }
                } else {
                    // Recursively search
                    $this->extractRoomsFromApolloState($value, $rooms, $depth + 1);
                }
            }
        }
    }

    private function detectWebsiteType($url)
    {
        // Detect website type from URL
        if (strpos($url, 'booking.com') !== false) return 'booking';
        if (strpos($url, 'agoda.com') !== false) return 'agoda';
        if (strpos($url, 'expedia.com') !== false || strpos($url, 'expedia.') !== false) return 'expedia';
        if (strpos($url, 'hotels.com') !== false) return 'hotels';
        if (strpos($url, 'traveloka.com') !== false) return 'traveloka';
        if (strpos($url, 'vntravel.com') !== false || strpos($url, 'vntravel.') !== false) return 'vntravel';
        if (strpos($url, 'mytour.vn') !== false || strpos($url, 'mytour.') !== false) return 'mytour';
        if (strpos($url, 'luxstay.com') !== false) return 'luxstay';
        
        return null;
    }

    private function getWebsiteName($websiteType)
    {
        $names = [
            'booking' => 'Booking.com',
            'agoda' => 'Agoda.com',
            'expedia' => 'Expedia',
            'hotels' => 'Hotels.com',
            'traveloka' => 'Traveloka',
            'vntravel' => 'VnTravel',
            'mytour' => 'Mytour.vn',
            'luxstay' => 'Luxstay',
        ];
        
        return $names[$websiteType] ?? 'Website';
    }

    private function getWebsitePrefix($websiteType)
    {
        $prefixes = [
            'booking' => 'BC-',
            'agoda' => 'AG-',
            'expedia' => 'EX-',
            'hotels' => 'HT-',
            'traveloka' => 'TK-',
            'vntravel' => 'VT-',
            'mytour' => 'MT-',
            'luxstay' => 'LS-',
        ];
        
        return $prefixes[$websiteType] ?? 'RM-';
    }

    private function getWebsiteHeaders($websiteType, $url)
    {
        // Base headers
        $baseHeaders = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9,vi;q=0.8',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'Cache-Control' => 'max-age=0',
        ];

        // Website-specific headers
        switch ($websiteType) {
            case 'booking':
                $baseHeaders['Referer'] = 'https://www.booking.com/';
                $baseHeaders['Sec-Fetch-Dest'] = 'document';
                $baseHeaders['Sec-Fetch-Mode'] = 'navigate';
                $baseHeaders['Sec-Fetch-Site'] = 'none';
                break;
            
            case 'agoda':
                $baseHeaders['Referer'] = 'https://www.agoda.com/';
                $baseHeaders['Sec-Fetch-Dest'] = 'document';
                $baseHeaders['Sec-Fetch-Mode'] = 'navigate';
                $baseHeaders['Sec-Fetch-Site'] = 'same-origin';
                break;
            
            case 'expedia':
                $baseHeaders['Referer'] = 'https://www.expedia.com/';
                $baseHeaders['Accept'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8';
                $baseHeaders['Accept-Language'] = 'en-US,en;q=0.9';
                $baseHeaders['Accept-Encoding'] = 'gzip, deflate, br';
                $baseHeaders['Sec-Fetch-Dest'] = 'document';
                $baseHeaders['Sec-Fetch-Mode'] = 'navigate';
                $baseHeaders['Sec-Fetch-Site'] = 'same-origin';
                $baseHeaders['Sec-Fetch-User'] = '?1';
                $baseHeaders['sec-ch-ua'] = '"Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"';
                $baseHeaders['sec-ch-ua-mobile'] = '?0';
                $baseHeaders['sec-ch-ua-platform'] = '"Windows"';
                break;
            
            case 'hotels':
                $baseHeaders['Referer'] = 'https://www.hotels.com/';
                break;
            
            case 'traveloka':
                $baseHeaders['Referer'] = 'https://www.traveloka.com/';
                $baseHeaders['Accept-Language'] = 'vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7';
                break;
            
            case 'vntravel':
                $baseHeaders['Referer'] = 'https://www.vntravel.com/';
                $baseHeaders['Accept-Language'] = 'vi-VN,vi;q=0.9';
                break;
            
            case 'mytour':
                $baseHeaders['Referer'] = 'https://www.mytour.vn/';
                $baseHeaders['Accept-Language'] = 'vi-VN,vi;q=0.9';
                break;
            
            case 'luxstay':
                $baseHeaders['Referer'] = 'https://www.luxstay.com/';
                $baseHeaders['Accept-Language'] = 'vi-VN,vi;q=0.9';
                break;
            
            default:
                // Extract domain from URL for referer
                $parsedUrl = parse_url($url);
                if (isset($parsedUrl['scheme']) && isset($parsedUrl['host'])) {
                    $baseHeaders['Referer'] = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/';
                }
        }

        return $baseHeaders;
    }

    private function extractRoomsByWebsiteType($websiteType, $html, $xpath, $url)
    {
        switch ($websiteType) {
            case 'booking':
                return $this->extractRoomsFromBooking($html, $xpath);
            case 'agoda':
                return $this->extractRoomsFromAgoda($html, $xpath, $url);
            case 'expedia':
                return $this->extractRoomsFromExpedia($html, $xpath);
            case 'hotels':
                return $this->extractRoomsFromHotels($html, $xpath);
            case 'traveloka':
                return $this->extractRoomsFromTraveloka($html, $xpath);
            case 'vntravel':
                return $this->extractRoomsFromVnTravel($html, $xpath);
            case 'mytour':
                return $this->extractRoomsFromMytour($html, $xpath);
            case 'luxstay':
                return $this->extractRoomsFromLuxstay($html, $xpath);
            default:
                return [];
        }
    }

    private function detectPageType($url, $html = '')
    {
        // Detect if URL is city page, hotel detail page, or search page
        if (strpos($url, '/city/') !== false) {
            return 'city';
        }
        if (strpos($url, '/hotel/') !== false || strpos($url, '/property/') !== false || strpos($url, '/rooms/') !== false) {
            return 'hotel';
        }
        if (strpos($url, '/search') !== false || strpos($url, 'searchresults') !== false) {
            return 'search';
        }
        
        // Try to detect from HTML content
        if (strpos($html, 'city') !== false && strpos($html, 'hotel-list') !== false) {
            return 'city';
        }
        
        return 'unknown';
    }

    // Generic room extraction methods for other websites
    private function extractRoomsFromExpedia($html, $xpath)
    {
        $rooms = [];
        
        // Try JSON-LD
        preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $jsonMatches);
        foreach ($jsonMatches[1] as $jsonStr) {
            $data = json_decode($jsonStr, true);
            if ($data && isset($data['@type']) && $data['@type'] === 'Hotel') {
                if (isset($data['containsPlace'])) {
                    foreach ($data['containsPlace'] as $place) {
                        if (isset($place['@type']) && $place['@type'] === 'HotelRoom') {
                            $rooms[] = $this->parseRoomFromJsonLd($place);
                        }
                    }
                }
            }
        }
        
        // Try to find room data in script tags
        preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $html, $scriptMatches);
        foreach ($scriptMatches[1] as $scriptContent) {
            if (preg_match('/"roomTypes?"\s*:\s*\[(.*?)\]/is', $scriptContent, $roomMatches)) {
                $roomData = $this->extractJsonArray($roomMatches[1]);
                if ($roomData) {
                    foreach ($roomData as $room) {
                        $rooms[] = $this->parseGenericRoomFromJson($room);
                    }
                }
            }
        }
        
        // Parse HTML
        if (empty($rooms)) {
            $rooms = $this->parseRoomsFromGenericHTML($xpath);
        }
        
        return $rooms;
    }

    private function extractRoomsFromHotels($html, $xpath)
    {
        return $this->extractRoomsFromExpedia($html, $xpath); // Similar structure
    }

    private function extractRoomsFromTraveloka($html, $xpath)
    {
        $rooms = [];
        
        // Traveloka often uses JSON in script tags
        preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $html, $scriptMatches);
        foreach ($scriptMatches[1] as $scriptContent) {
            if (preg_match('/"roomTypes?"\s*:\s*\[(.*?)\]/is', $scriptContent, $roomMatches)) {
                $roomData = $this->extractJsonArray($roomMatches[1]);
                if ($roomData) {
                    foreach ($roomData as $room) {
                        $rooms[] = $this->parseGenericRoomFromJson($room);
                    }
                }
            }
        }
        
        if (empty($rooms)) {
            $rooms = $this->parseRoomsFromGenericHTML($xpath);
        }
        
        return $rooms;
    }

    private function extractRoomsFromVnTravel($html, $xpath)
    {
        return $this->extractRoomsFromTraveloka($html, $xpath);
    }

    private function extractRoomsFromMytour($html, $xpath)
    {
        return $this->extractRoomsFromTraveloka($html, $xpath);
    }

    private function extractRoomsFromLuxstay($html, $xpath)
    {
        return $this->extractRoomsFromTraveloka($html, $xpath);
    }

    private function parseGenericRoomFromJson($data)
    {
        return [
            'room_number' => $data['name'] ?? $data['roomName'] ?? $data['title'] ?? $data['roomTypeName'] ?? $data['room_type_name'] ?? null,
            'room_type' => $this->extractRoomType($data['name'] ?? $data['roomName'] ?? $data['type'] ?? 'Standard'),
            'capacity' => $data['maxOccupancy'] ?? $data['occupancy'] ?? $data['capacity'] ?? $data['max_occupancy'] ?? $data['guestCount'] ?? 2,
            'price_per_night' => $this->extractGenericPrice($data),
            'description' => $data['description'] ?? $data['roomDescription'] ?? $data['room_description'] ?? '',
            'amenities' => $this->extractGenericAmenities($data),
            'images' => $this->extractGenericImages($data),
        ];
    }

    private function extractGenericPrice($data)
    {
        if (isset($data['price'])) {
            if (is_array($data['price'])) {
                return (float)($data['price']['amount'] ?? $data['price']['value'] ?? 0);
            }
            return (float)$data['price'];
        }
        if (isset($data['rate'])) return (float)$data['rate'];
        if (isset($data['pricePerNight'])) return (float)$data['pricePerNight'];
        if (isset($data['basePrice'])) return (float)$data['basePrice'];
        return 0;
    }

    private function extractGenericAmenities($data)
    {
        $amenities = [];
        if (isset($data['amenities']) && is_array($data['amenities'])) {
            foreach ($data['amenities'] as $amenity) {
                if (is_string($amenity)) {
                    $amenities[] = $amenity;
                } elseif (isset($amenity['name'])) {
                    $amenities[] = $amenity['name'];
                }
            }
        }
        return array_unique($amenities);
    }

    private function extractGenericImages($data)
    {
        $images = [];
        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $img) {
                if (is_string($img)) {
                    $images[] = $img;
                } elseif (isset($img['url'])) {
                    $images[] = $img['url'];
                }
            }
        }
        return array_filter($images);
    }

    private function parseRoomsFromGenericHTML($xpath)
    {
        $rooms = [];
        
        // Generic selectors for room elements
        $selectors = [
            "//div[contains(@class, 'room')]",
            "//div[contains(@class, 'Room')]",
            "//div[contains(@data-testid, 'room')]",
            "//div[contains(@id, 'room')]",
        ];
        
        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes && $nodes->length > 0) {
                foreach ($nodes as $node) {
                    $name = $xpath->evaluate("string(.//h2 | .//h3 | .//h4 | .//span[contains(@class, 'name')])", $node);
                    if ($name && trim($name)) {
                        $rooms[] = [
                            'room_number' => trim($name),
                            'room_type' => $this->extractRoomType($name),
                            'capacity' => 2,
                            'price_per_night' => 0,
                            'description' => '',
                            'amenities' => [],
                            'images' => $this->extractImagesFromNode($xpath, $node),
                        ];
                    }
                }
                break;
            }
        }
        
        return $rooms;
    }

    private function parseAgodaRoomFromJson($data)
    {
        return [
            'room_number' => $data['name'] ?? $data['roomName'] ?? $data['title'] ?? $data['roomTypeName'] ?? null,
            'room_type' => $this->extractRoomType($data['name'] ?? $data['roomName'] ?? $data['type'] ?? 'Standard'),
            'capacity' => $data['maxOccupancy'] ?? $data['occupancy'] ?? $data['capacity'] ?? $data['guestCount'] ?? 2,
            'price_per_night' => $this->extractAgodaPrice($data),
            'description' => $data['description'] ?? $data['roomDescription'] ?? '',
            'amenities' => $this->extractAgodaAmenities($data),
            'images' => $this->extractAgodaImages($data),
        ];
    }

    private function extractAgodaPrice($data)
    {
        // Agoda price might be in different formats
        if (isset($data['price'])) {
            if (is_array($data['price'])) {
                return (float)($data['price']['amount'] ?? $data['price']['value'] ?? 0);
            }
            return (float)$data['price'];
        }
        if (isset($data['rate'])) {
            return (float)$data['rate'];
        }
        if (isset($data['pricePerNight'])) {
            return (float)$data['pricePerNight'];
        }
        if (isset($data['basePrice'])) {
            return (float)$data['basePrice'];
        }
        return 0;
    }

    private function extractAgodaAmenities($data)
    {
        $amenities = [];
        if (isset($data['amenities']) && is_array($data['amenities'])) {
            foreach ($data['amenities'] as $amenity) {
                if (is_string($amenity)) {
                    $amenities[] = $amenity;
                } elseif (isset($amenity['name'])) {
                    $amenities[] = $amenity['name'];
                } elseif (isset($amenity['title'])) {
                    $amenities[] = $amenity['title'];
                }
            }
        }
        if (isset($data['facilities']) && is_array($data['facilities'])) {
            foreach ($data['facilities'] as $facility) {
                if (is_string($facility)) {
                    $amenities[] = $facility;
                } elseif (isset($facility['name'])) {
                    $amenities[] = $facility['name'];
                }
            }
        }
        return array_unique($amenities);
    }

    private function extractAgodaImages($data)
    {
        $images = [];
        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $img) {
                if (is_string($img)) {
                    $images[] = $img;
                } elseif (isset($img['url'])) {
                    $images[] = $img['url'];
                } elseif (isset($img['src'])) {
                    $images[] = $img['src'];
                } elseif (isset($img['imageUrl'])) {
                    $images[] = $img['imageUrl'];
                }
            }
        }
        if (isset($data['image']) && is_string($data['image'])) {
            $images[] = $data['image'];
        }
        if (isset($data['thumbnail']) && is_string($data['thumbnail'])) {
            $images[] = $data['thumbnail'];
        }
        return array_filter($images);
    }

    private function parseRoomsFromAgodaHTML($xpath)
    {
        $rooms = [];
        
        // Try to find Agoda-specific room elements
        // Agoda uses various class names for room cards
        $selectors = [
            "//div[contains(@class, 'RoomCard')]",
            "//div[contains(@class, 'room-card')]",
            "//div[contains(@class, 'PropertyRoomType')]",
            "//div[contains(@class, 'property-room-type')]",
            "//div[contains(@class, 'room-type')]",
            "//div[contains(@class, 'RoomType')]",
            "//div[contains(@class, 'RoomCardContainer')]",
            "//div[contains(@class, 'RoomTypeCard')]",
            "//div[contains(@data-testid, 'room')]",
            "//div[contains(@data-testid, 'Room')]",
            "//div[contains(@data-testid, 'room-type')]",
            "//div[contains(@id, 'room')]",
            "//div[contains(@id, 'Room')]",
            "//div[contains(@class, 'room') and contains(@class, 'card')]",
            "//div[contains(@class, 'Room') and contains(@class, 'Card')]",
        ];
        
        $roomNodes = null;
        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes && $nodes->length > 0) {
                $roomNodes = $nodes;
                break;
            }
        }
        
        if (!$roomNodes || $roomNodes->length === 0) {
            // Try to find any element with room-related attributes
            $roomNodes = $xpath->query("//div[contains(@class, 'room') or contains(@class, 'Room')]");
        }
        
        foreach ($roomNodes as $node) {
            $name = $xpath->evaluate("string(.//h2 | .//h3 | .//h4 | .//h5 | .//span[contains(@class, 'name')] | .//div[contains(@class, 'name')] | .//a[contains(@class, 'room-name')] | .//span[contains(@data-testid, 'room-name')] | .//div[contains(@data-testid, 'room-name')])", $node);
            $price = $xpath->evaluate("string(.//span[contains(@class, 'price')] | .//div[contains(@class, 'price')] | .//span[contains(@class, 'Price')] | .//b[contains(@class, 'price')] | .//span[contains(@data-testid, 'price')] | .//div[contains(@data-testid, 'price')])", $node);
            $description = $xpath->evaluate("string(.//p | .//div[contains(@class, 'description')] | .//div[contains(@class, 'room-description')] | .//div[contains(@class, 'RoomDescription')])", $node);
            
            if ($name && trim($name)) {
                $rooms[] = [
                    'room_number' => trim($name),
                    'room_type' => $this->extractRoomType($name),
                    'capacity' => $this->extractCapacityFromText($xpath->evaluate("string(.)", $node)),
                    'price_per_night' => $this->parsePrice($price),
                    'description' => trim($description),
                    'amenities' => [],
                    'images' => $this->extractImagesFromNode($xpath, $node),
                ];
            }
        }
        
        return $rooms;
    }

    private function extractCapacityFromText($text)
    {
        // Try to extract capacity from text like "2 guests", "2 người", etc.
        if (preg_match('/(\d+)\s*(guest|người|person|people)/i', $text, $matches)) {
            return (int)$matches[1];
        }
        return 2; // Default
    }

    private function parseRoomFromJsonLd($data)
    {
        return [
            'room_number' => $data['name'] ?? null,
            'room_type' => $this->extractRoomType($data['name'] ?? 'Standard'),
            'capacity' => $this->extractCapacity($data),
            'price_per_night' => $this->extractPrice($data),
            'description' => $data['description'] ?? '',
            'amenities' => $this->extractAmenities($data),
            'images' => $this->extractImages($data),
        ];
    }

    private function parseRoomFromJson($data)
    {
        return [
            'room_number' => $data['name'] ?? $data['title'] ?? null,
            'room_type' => $this->extractRoomType($data['name'] ?? $data['type'] ?? 'Standard'),
            'capacity' => $data['capacity'] ?? $data['maxOccupancy'] ?? 2,
            'price_per_night' => $this->extractPriceFromData($data),
            'description' => $data['description'] ?? '',
            'amenities' => $data['amenities'] ?? $data['facilities'] ?? [],
            'images' => $this->extractImagesFromData($data),
        ];
    }

    private function parseRoomsFromHTML($xpath)
    {
        $rooms = [];
        
        // Try to find room cards/items
        $roomNodes = $xpath->query("//div[contains(@class, 'room') or contains(@class, 'property')]");
        
        foreach ($roomNodes as $node) {
            $name = $xpath->evaluate("string(.//h2 | .//h3 | .//span[contains(@class, 'name')])", $node);
            $price = $xpath->evaluate("string(.//span[contains(@class, 'price')] | .//div[contains(@class, 'price')])", $node);
            
            if ($name) {
                $rooms[] = [
                    'room_number' => $name,
                    'room_type' => $this->extractRoomType($name),
                    'capacity' => 2,
                    'price_per_night' => $this->parsePrice($price),
                    'description' => $xpath->evaluate("string(.//p | .//div[contains(@class, 'description')])", $node),
                    'amenities' => [],
                    'images' => $this->extractImagesFromNode($xpath, $node),
                ];
            }
        }
        
        return $rooms;
    }

    private function extractRoomType($name)
    {
        $name = strtolower($name);
        if (strpos($name, 'suite') !== false) return 'Suite';
        if (strpos($name, 'deluxe') !== false) return 'Deluxe';
        if (strpos($name, 'vip') !== false) return 'VIP';
        if (strpos($name, 'standard') !== false) return 'Standard';
        return 'Standard';
    }

    private function extractCapacity($data)
    {
        if (isset($data['occupancy'])) {
            if (isset($data['occupancy']['maxValue'])) {
                return (int)$data['occupancy']['maxValue'];
            }
        }
        if (isset($data['bedDetails'])) {
            // Try to calculate from bed details
            return 2; // Default
        }
        return 2;
    }

    private function extractPrice($data)
    {
        if (isset($data['offers']['price'])) {
            return (float)$data['offers']['price'];
        }
        return 0;
    }

    private function extractPriceFromData($data)
    {
        if (isset($data['price'])) return (float)$data['price'];
        if (isset($data['pricePerNight'])) return (float)$data['pricePerNight'];
        if (isset($data['rate'])) return (float)$data['rate'];
        return 0;
    }

    private function parsePrice($priceStr)
    {
        // Remove currency symbols and extract number
        $priceStr = preg_replace('/[^\d.,]/', '', $priceStr);
        $priceStr = str_replace(',', '', $priceStr);
        return (float)$priceStr;
    }

    private function extractAmenities($data)
    {
        $amenities = [];
        if (isset($data['amenityFeature'])) {
            foreach ($data['amenityFeature'] as $amenity) {
                if (isset($amenity['name'])) {
                    $amenities[] = $amenity['name'];
                }
            }
        }
        return $amenities;
    }

    private function extractImages($data)
    {
        $images = [];
        if (isset($data['image'])) {
            if (is_array($data['image'])) {
                foreach ($data['image'] as $img) {
                    if (is_string($img)) {
                        $images[] = $img;
                    } elseif (isset($img['url'])) {
                        $images[] = $img['url'];
                    }
                }
            } elseif (is_string($data['image'])) {
                $images[] = $data['image'];
            }
        }
        return $images;
    }

    private function extractImagesFromData($data)
    {
        $images = [];
        if (isset($data['images']) && is_array($data['images'])) {
            $images = $data['images'];
        } elseif (isset($data['image'])) {
            $images = is_array($data['image']) ? $data['image'] : [$data['image']];
        }
        return array_filter($images);
    }

    private function extractImagesFromNode($xpath, $node)
    {
        $images = [];
        $imgNodes = $xpath->query(".//img", $node);
        foreach ($imgNodes as $img) {
            $src = $img->getAttribute('src') ?: $img->getAttribute('data-src');
            if ($src && filter_var($src, FILTER_VALIDATE_URL)) {
                $images[] = $src;
            }
        }
        return $images;
    }

    private function downloadImage($url, $roomId)
    {
        try {
            $response = Http::timeout(10)->get($url);
            if ($response->successful()) {
                $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $filename = 'rooms/booking_' . $roomId . '_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
                Storage::disk('public')->put($filename, $response->body());
                return $filename;
            }
        } catch (\Exception $e) {
            Log::warning("Failed to download image", ['url' => $url, 'error' => $e->getMessage()]);
        }
        return null;
    }
}
