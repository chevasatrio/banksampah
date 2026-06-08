<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransaksiSetorRequest extends FormRequest
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
            'catatan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.jenis_sampah_id' => ['required', 'exists:jenis_sampahs,id'],
            'items.*.berat_kg' => ['required', 'numeric', 'min:0.01'],
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
            'items.required' => 'Minimal 1 jenis sampah harus diisi.',
            'items.min' => 'Minimal 1 jenis sampah harus diisi.',
            'items.*.jenis_sampah_id.required' => 'Jenis sampah wajib dipilih.',
            'items.*.jenis_sampah_id.exists' => 'Jenis sampah tidak valid.',
            'items.*.berat_kg.required' => 'Berat sampah wajib diisi.',
            'items.*.berat_kg.numeric' => 'Berat sampah harus berupa angka.',
            'items.*.berat_kg.min' => 'Berat sampah minimal 0.01 KG.',
        ];
    }
}
