<?php

namespace App\Http\Requests\Teams;

use App\Enums\TeamRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * El role asignable debe pertenecer al enum TeamRole (excluye owner).
     * Los modelos Membership y TeamInvitation tienen cast
     * `'role' => TeamRole::class`, por lo que persistir un slug fuera del
     * enum rompería la hidratación de Eloquent. Por eso NO se aceptan roles
     * personalizados de la tabla `roles` como valor asignable.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(array_column(TeamRole::assignable(), 'value'))],
        ];
    }
}
