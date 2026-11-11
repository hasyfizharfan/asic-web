<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Registration extends Model
{
    use HasFactory;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'registration';

    /**
     * Primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_registration';

    /**
     * Tipe data primary key.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Auto incrementing primary key.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Menggunakan kolom custom registration_date, bukan timestamps bawaan Laravel.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Atribut yang dapat diisi (mass assignable).
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_participant',
        'id_event',
        'id_period',
        'registration_type',
        'attendance_mode',
        'participation_type',
        'publication_option',
        'registration_date',
        'registration_status',
    ];

    /**
     * Cast atribut ke tipe data tertentu.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'registration_date' => 'datetime',
        ];
    }

    /**
     * Relasi belongsTo ke Participant.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'id_participant', 'id_participant');
    }

    /**
     * Relasi belongsTo ke Event.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event', 'id_event');
    }

    /**
     * Relasi belongsTo ke RegistrationPeriod.
     */
    public function registrationPeriod(): BelongsTo
    {
        return $this->belongsTo(RegistrationPeriod::class, 'id_period', 'id_period');
    }

    /**
     * Relasi hasMany ke RegistrationAddOn.
     */
    public function registrationAddOns(): HasMany
    {
        return $this->hasMany(RegistrationAddOn::class, 'id_registration', 'id_registration');
    }

    /**
     * Relasi belongsToMany ke AddOn melalui pivot registration_add_on.
     */
    public function addOns(): BelongsToMany
    {
        return $this->belongsToMany(AddOn::class, 'registration_add_on', 'id_registration', 'id_add_on')
            ->withPivot('jumlah', 'subtotal');
    }

    /**
     * Relasi hasOne ke AbstractSubmission (1:1).
     */
    public function abstractSubmission(): HasOne
    {
        return $this->hasOne(AbstractSubmission::class, 'id_registration', 'id_registration');
    }

    /**
     * Relasi hasOne ke Attendance (1:1).
     */
    public function attendance(): HasOne
    {
        return $this->hasOne(Attendance::class, 'id_registration', 'id_registration');
    }

    /**
     * Relasi hasMany ke ParticipantFile (1:N).
     */
    public function participantFiles(): HasMany
    {
        return $this->hasMany(ParticipantFile::class, 'id_registration', 'id_registration');
    }

    /**
     * Helper untuk memeriksa apakah registrasi merupakan presenter (Oral/Poster).
     */
    public function isPresenter(): bool
    {
        return in_array($this->participation_type, ['Oral Presenter', 'Poster Presenter'], true);
    }

    /**
     * Helper untuk memeriksa apakah kehadiran luring (offline).
     */
    public function isLuring(): bool
    {
        return $this->attendance_mode === 'Luring';
    }
}

