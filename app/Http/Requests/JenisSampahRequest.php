<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JenisSampahRequest extends FormRequest
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
        return [
            'nama' => ['required', 'string', 'max:255'],
            'harga_per_kg' => ['required', 'numeric', 'min:0'],
            'kategori_id' => ['required', 'exists:kategori_sampahs,id'],
            'is_active' => ['sometimes', 'boolean'],
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
            'nama.required' => 'Nama jenis sampah wajib diisi.',
            'harga_per_kg.required' => 'Harga per KG wajib diisi.',
            'harga_per_kg.numeric' => 'Harga per KG harus berupa angka.',
            'harga_per_kg.min' => 'Harga per KG tidak boleh negatif.',
            'kategori_id.required' => 'Kategori sampah wajib dipilih.',
            'kategori_id.exists' => 'Kategori sampah tidak valid.',
        ];
    }
}
