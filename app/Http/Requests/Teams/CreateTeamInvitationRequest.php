<?php

namespace App\Http\Requests\Teams;

use App\Enums\TeamRole;
use App\Rules\UniqueTeamInvitation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTeamInvitationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * El role debe ser un slug definido en el enum TeamRole. Los modelos
     * TeamInvitation y Membership tienen cast `'role' => TeamRole::class`,
     * por lo que no se pueden persistir roles fuera del enum sin romper
     * la hidratación de Eloquent.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', new UniqueTeamInvitation($this->route('team'))],
            'role' => ['required', 'string', Rule::enum(TeamRole::class)],
        ];
    }
}
