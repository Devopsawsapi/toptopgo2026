<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\UpdateDocumentsRequest;
use App\Http\Resources\Driver\DriverResource;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Storage;

class DriverProfileController extends Controller
{
    public function __construct(private FileUploadService $fileUploadService) {}

    public function show(Request $request)
    {
        $driver = $request->user()->load('wallet', 'latestLocation');

        $reviews = Review::where('driver_id', $driver->id)->get();

        $avgRating = $reviews->avg('rating') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $driver->id,
                'first_name' => $driver->first_name,
                'last_name' => $driver->last_name,
                'email' => $driver->email,
                'phone' => $driver->phone,

                'profile_photo' => $driver->profile_photo
                    ? asset('storage/' . $driver->profile_photo)
                    : null,

                'vehicle_brand' => $driver->vehicle_brand,
                'vehicle_model' => $driver->vehicle_model,
                'vehicle_color' => $driver->vehicle_color,
                'vehicle_country' => $driver->vehicle_country,
                'vehicle_city' => $driver->vehicle_city,

                'average_rating' => round($avgRating, 1),
                'rating_count' => $reviews->count()
            ]
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'vehicle_brand' => 'sometimes|string|max:100',
            'vehicle_model' => 'sometimes|string|max:100',
            'vehicle_color' => 'sometimes|string|max:50',
            'vehicle_country' => 'sometimes|string|max:100',
            'vehicle_city' => 'sometimes|string|max:100',
        ]);

        $request->user()->update($request->only([
            'first_name',
            'last_name',
            'vehicle_brand',
            'vehicle_model',
            'vehicle_color',
            'vehicle_country',
            'vehicle_city',
        ]));

        return new DriverResource($request->user()->fresh());
    }

    public function updateDocuments(UpdateDocumentsRequest $request)
    {
        $driver = $request->user();
        $data = [];

        $fields = [
            'id_card_front',
            'id_card_back',
            'license_front',
            'license_back',
            'vehicle_registration',
            'insurance',
        ];

        foreach ($fields as $field) {

            if ($request->hasFile($field)) {

                $data[$field] = $this->fileUploadService->uploadDocument(
                    $request->file($field),
                    $driver->id,
                    $field
                );
            }
        }

        $driver->update($data);

        return response()->json([
            'message' => 'Documents mis à jour. En attente de validation.',
            'driver' => new DriverResource($driver->fresh()),
        ]);
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:3072'
        ]);

        $driver = $request->user();

        if ($driver->profile_photo) {

            Storage::disk('public')->delete($driver->profile_photo);
        }

        $path = $request->file('photo')->store('drivers/photos', 'public');

        $driver->update([
            'profile_photo' => $path
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Photo mise à jour.',
            'profile_photo' => asset('storage/' . $path)
        ]);
    }
}