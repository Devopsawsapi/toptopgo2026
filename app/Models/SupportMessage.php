<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\AdminUser;

class SupportMessage extends Model
{
    protected $fillable = [
        'admin_id', 'recipient_type', 'recipient_id',
        'content', 'is_read', 'read_at',
    ];

    public function admin() { return $this->belongsTo(AdminUser::class); }
    public function recipient() { return $this->morphTo(); }
}
