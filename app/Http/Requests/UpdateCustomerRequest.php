<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama pelanggan harus diisi.',
            'name.max' => 'Nama pelanggan maksimal 150 karakter.',
            'phone.max' => 'Nomor telepon maksimal 30 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 100 karakter.',
            'address.max' => 'Alamat maksimal 500 karakter.',
            'notes.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Pelanggan',
            'phone' => 'Nomor Telepon',
            'email' => 'Email',
            'address' => 'Alamat',
            'notes' => 'Catatan',
        ];
    }
}
