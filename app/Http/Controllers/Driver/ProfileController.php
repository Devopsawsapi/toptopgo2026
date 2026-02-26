<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\UpdateDocumentsRequest;
use App\Http\Resources\Driver\DriverResource;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct(private FileUploadService $fileUploadService) {}

    public function show(Request $request)
    {
        return new DriverResource($request->user()->load('wallet', 'latestLocation'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'first_name'     => 'sometimes|string|max:100',
            'last_name'      => 'sometimes|string|max:100',
            'vehicle_brand'  => 'sometimes|string|max:100',
            'vehicle_model'  => 'sometimes|string|max:100',
            'vehicle_color'  => 'sometimes|string|max:50',
            'vehicle_country'=> 'sometimes|string|max:100',
            'vehicle_city'   => 'sometimes|string|max:100',
        ]);

        $request->user()->update($request->only([
            'first_name', 'last_name',
            'vehicle_brand', 'vehicle_model', 'vehicle_color',
            'vehicle_country', 'vehicle_city',
        ]));

        return new DriverResource($request->user()->fresh());
    }

    public function updateDocuments(UpdateDocumentsRequest $request)
    {
        $driver = $request->user();
        $data   = [];

        $fields = [
            'id_card_front', 'id_card_back',
            'license_front', 'license_back',
            'vehicle_registration', 'insurance',
        ];

        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $this->fileUploadService->uploadDocument(
                    $request->file($field), $driver->id, $field
                );
            }
        }

        // Champs texte
        $textFields = [
            'id_card_issue_date', 'id_card_expiry_date', 'id_card_issue_city', 'id_card_issue_country',
            'license_issue_date', 'license_expiry_date', 'license_issue_city', 'license_issue_country',
        ];

        foreach ($textFields as $f) {
            if ($request->filled($f)) {
                $data[$f] = $request->input($f);
            }
        }

        $driver->update($data);

        return response()->json([
            'message' => 'Documents mis à jour. En attente de validation.',
            'driver'  => new DriverResource($driver->fresh()),
        ]);
    }

    public function updatePhoto(Request $request)
    {
        $request->validate(['photo' => 'required|image|max:3072']);
        $path = $this->fileUploadService->uploadProfilePhoto($request->file('photo'), 'drivers');
        $request->user()->update(['profile_photo' => $path]);
        return response()->json(['message' => 'Photo mise à jour.', 'path' => $path]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:6|confirmed',
        ]);

        $driver = $request->user();

        if (!Hash::check($request->current_password, $driver->password)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect.'], 422);
        }

        $driver->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Mot de passe modifié avec succès.']);
    }
}
