<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DosyaController extends Controller
{
    /** Galeri bazlı gizli disk: belgeler yalnızca controller üzerinden sunulur. */
    private const DISK = 'local';

    public function store(Request $request, Vehicle $arac): RedirectResponse
    {
        $data = $request->validate([
            'kind' => ['required', Rule::in(['foto', 'belge'])],
            'doc_type' => ['nullable', Rule::in(array_keys(VehicleFile::DOC_TYPES))],
            'files' => ['required', 'array', 'max:20'],
            'files.*' => ['file', 'max:15360', 'mimes:jpg,jpeg,png,webp,heic,pdf'],
        ], [
            'files.required' => 'En az bir dosya seçin.',
            'files.*.max' => 'Her dosya en fazla 15 MB olabilir.',
            'files.*.mimes' => 'Yalnızca JPG, PNG, WEBP, HEIC ve PDF yüklenebilir.',
        ]);

        $docType = $data['kind'] === 'belge' ? ($data['doc_type'] ?? 'diger') : null;

        foreach ($request->file('files') as $file) {
            $path = $file->store('araclar/'.$arac->id, self::DISK);

            $arac->files()->create([
                'kind' => $data['kind'],
                'doc_type' => $docType,
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                'path' => $path,
                'mime' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
                'created_by' => $request->user()->id,
            ]);
        }

        $n = count($data['files']);

        return redirect()->route('araclar.show', $arac)->with('ok', $n.' dosya yüklendi.');
    }

    /** Dosyayı tarayıcıda göster (fotoğraf satır içi, PDF açılır). */
    public function goster(Vehicle $arac, VehicleFile $dosya): StreamedResponse
    {
        abort_unless($dosya->vehicle_id === $arac->id, 404);
        abort_unless(Storage::disk(self::DISK)->exists($dosya->path), 404);

        return Storage::disk(self::DISK)->response($dosya->path, $dosya->original_name, [
            'Content-Type' => $dosya->mime,
            'Content-Disposition' => 'inline; filename="'.addslashes($dosya->original_name).'"',
        ]);
    }

    public function destroy(Vehicle $arac, VehicleFile $dosya): RedirectResponse
    {
        abort_unless($dosya->vehicle_id === $arac->id, 404);

        Storage::disk(self::DISK)->delete($dosya->path);
        $dosya->delete();

        return redirect()->route('araclar.show', $arac)->with('ok', 'Dosya silindi.');
    }
}
