<?php

namespace App\Models\Concerns;

use App\Models\Gallery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Her galeri yalnızca kendi kayıtlarını görür. Cardino yöneticisi (admin) hepsini görür.
 */
trait BelongsToGallery
{
    public static function bootBelongsToGallery(): void
    {
        static::addGlobalScope('gallery', function (Builder $query) {
            $user = Auth::user();
            if ($user && ! $user->isAdmin()) {
                $query->where($query->getModel()->getTable().'.gallery_id', $user->gallery_id ?? 0);
            }
        });

        static::creating(function ($model) {
            $user = Auth::user();
            if ($user && ! $user->isAdmin()) {
                $model->gallery_id = $user->gallery_id;
            }
        });
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }
}
