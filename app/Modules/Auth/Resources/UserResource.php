<?php

namespace App\Modules\Auth\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'email'               => $this->email,
            'phone'               => $this->phone,
            'avatar'              => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'language_preference' => $this->language_preference,
            'is_email_verified'   => $this->is_email_verified,
            'is_phone_verified'   => $this->is_phone_verified,
            'roles'               => $this->getRoleNames(),
            'has_business'        => $this->hasBusiness(),
            'business_slug'       => $this->business?->slug,
            'created_at'          => $this->created_at,
        ];
    }
}
