<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeanCalibration extends Model
{
    protected $fillable = [
        'dean_id',
        'ipcr_submission_id',
        'calibration_data',
        'overall_score',
        'status',
    ];

    protected $casts = [
        'calibration_data' => 'array',
        'overall_score' => 'decimal:2',
    ];

    public function dean(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dean_id');
    }

    public function ipcrSubmission(): BelongsTo
    {
        return $this->belongsTo(IpcrSubmission::class);
    }
}
