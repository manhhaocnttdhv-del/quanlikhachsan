<?php

namespace App\Imports;

use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class RoomsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithBatchInserts, WithChunkReading
{
    use SkipsFailures;

    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['so_phong']) && empty($row['room_number'])) {
            return null;
        }

        $roomNumber = $row['so_phong'] ?? $row['room_number'] ?? null;
        $roomType = $row['loai_phong'] ?? $row['room_type'] ?? 'Standard';
        $capacity = $row['suc_chua'] ?? $row['capacity'] ?? $row['so_nguoi'] ?? 2;
        $pricePerNight = $row['gia_dem'] ?? $row['price_per_night'] ?? $row['gia'] ?? 0;
        $description = $row['mo_ta'] ?? $row['description'] ?? '';
        $status = $row['trang_thai'] ?? $row['status'] ?? 'available';
        $imageUrl = $row['link_hinh_anh'] ?? $row['image_url'] ?? $row['hinh_anh'] ?? $row['image'] ?? null;
        
        // Parse amenities if provided
        $amenities = [];
        if (isset($row['tien_nghi']) || isset($row['amenities'])) {
            $amenitiesStr = $row['tien_nghi'] ?? $row['amenities'] ?? '';
            if ($amenitiesStr) {
                $amenities = array_map('trim', explode(',', $amenitiesStr));
                $amenities = array_filter($amenities);
            }
        }

        // Check if room number already exists
        $existingRoom = Room::where('room_number', $roomNumber)->first();
        if ($existingRoom) {
            // Generate unique room number
            $roomNumber = $roomNumber . '-' . time() . '-' . rand(1000, 9999);
        }

        // Validate status
        if (!in_array($status, ['available', 'occupied', 'maintenance'])) {
            $status = 'available';
        }

        $room = new Room([
            'room_number' => $roomNumber,
            'room_type' => $roomType,
            'capacity' => (int)$capacity,
            'price_per_night' => (float)$pricePerNight,
            'description' => $description,
            'amenities' => $amenities,
            'status' => $status,
        ]);
        
        // Save room first to get ID
        $room->save();
        
        // Handle image URL if provided
        if ($imageUrl) {
            $this->handleImage($room, $imageUrl);
        }
        
        return $room;
    }
    
    private function handleImage($room, $imageUrl)
    {
        try {
            // Check if it's a URL or file path
            if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                // Download image from URL
                $response = Http::timeout(10)->get($imageUrl);
                if ($response->successful()) {
                    $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                    $filename = 'rooms/import_' . $room->id . '_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
                    Storage::disk('public')->put($filename, $response->body());
                    
                    // Create RoomImage
                    RoomImage::create([
                        'room_id' => $room->id,
                        'image_path' => $filename,
                        'order' => 0,
                        'is_primary' => true,
                    ]);
                    
                    // Update room's main image
                    $room->update(['image' => $filename]);
                }
            } elseif (file_exists($imageUrl)) {
                // Copy file from local path
                $extension = pathinfo($imageUrl, PATHINFO_EXTENSION) ?: 'jpg';
                $filename = 'rooms/import_' . $room->id . '_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
                Storage::disk('public')->put($filename, file_get_contents($imageUrl));
                
                // Create RoomImage
                RoomImage::create([
                    'room_id' => $room->id,
                    'image_path' => $filename,
                    'order' => 0,
                    'is_primary' => true,
                ]);
                
                // Update room's main image
                $room->update(['image' => $filename]);
            }
        } catch (\Exception $e) {
            Log::warning("Failed to handle image for room {$room->id}", [
                'image_url' => $imageUrl,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'so_phong' => 'nullable',
            'room_number' => 'nullable',
            'loai_phong' => 'nullable',
            'room_type' => 'nullable',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}

