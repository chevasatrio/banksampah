<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransaksiTarikRequest extends FormRequest
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
            'nasabah_id' => ['required', 'exists:nasabahs,id'],
            'jumlah' => ['required', 'numeric', 'min:1'],
            'keterangan' => ['nullable', 'string'],
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
            'nasabah_id.required' => 'Nasabah wajib dipilih.',
            'nasabah_id.exists' => 'Nasabah tidak ditemukan.',
            'jumlah.required' => 'Jumlah penarikan wajib diisi.',
            'jumlah.numeric' => 'Jumlah penarikan harus berupa angka.',
            'jumlah.min' => 'Jumlah penarikan minimal Rp 1.',
        ];
    }
}
