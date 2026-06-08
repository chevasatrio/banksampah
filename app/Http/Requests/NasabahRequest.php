<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NasabahRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $nasabahId = $this->route('nasabah');

        return [
            'nama' => ['required', 'string', 'max:255'],
            'nik' => [
                'required',
                'string',
                'size:16',
                Rule::unique('nasabahs', 'nik')->ignore($nasabahId),
            ],
            'alamat' => ['required', 'string'],
            'no_hp' => ['required', 'string', 'max:15'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama nasabah wajib diisi.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.size' => 'NIK harus terdiri dari 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'alamat.required' => 'Alamat wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
        ];
    }
}
