<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    public const ROLES = ['Administrator', 'Staff', 'Student'];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'role',
        'profile_photo_path',
        'qr_code_path',
        'student_number',
        'phone',
        'program',
        'year_level',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function dashboardRouteName(): string
    {
        return match ($this->role) {
            'Administrator' => 'dashboard.administrator',
            'Staff' => 'dashboard.staff',
            'Student' => 'dashboard.student',
            default => abort(403, 'Your account has no valid role.'),
        };
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function profilePhotoUrl(): ?string
    {
        return $this->profile_photo_path ? Storage::disk('public')->url($this->profile_photo_path) : null;
    }

    public function qrCodeUrl(): ?string
    {
        return $this->qr_code_path ? Storage::disk('public')->url($this->qr_code_path) : null;
    }

    public function profileInitials(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->filter()
            ->map(fn (string $part): string => strtoupper($part[0]))
            ->take(2)
            ->implode('');
    }

    public function qrCodePayload(): string
    {
        return 'eventive-student|name:'.$this->name.'|id:'.$this->id.'|student-number:'.$this->student_number.'|email:'.$this->email.'|phone:'.$this->phone.'|program:'.$this->program.'|year-level:'.$this->year_level;
    }

    public function ensureQrCode(): void
    {
        if ($this->role !== 'Student') {
            return;
        }

        $path = $this->qr_code_path ?: 'qr-codes/students/'.$this->id.'.svg';

        $writer = new QRCode(new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel' => 'L',
            'margin' => 2,
        ]));

        $svg = $writer->render($this->qrCodePayload());

        if (str_starts_with($svg, 'data:image/svg+xml;base64,')) {
            $svg = base64_decode(substr($svg, 26), true);
        }

        Storage::disk('public')->put($path, $svg);
        $this->qr_code_path = $path;
        $this->save();
    }

    protected static function booted(): void
    {
        static::created(function (self $user): void {
            if ($user->role === 'Student') {
                $user->ensureQrCode();
            }
        });

    }
}
