<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRepairServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'customer_name' => [
                'required',
                'string',
                'max:100',
            ],
            'customer_phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'device_type' => [
                'required',
                'string',
                'in:hp,laptop',
            ],
            'device_model' => [
                'nullable',
                'string',
                'max:100',
            ],
            'issue_description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'service_fee' => [
                'required',
                'integer',
                'min:0',
            ],
            'sparepart_cost' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'sparepart_description' => [
                'nullable',
                'string',
                'max:255',
            ],
            'account_id' => [
                'required',
                'exists:accounts,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute harus diisi.',
            'integer' => ':attribute harus berupa angka.',
            'min' => ':attribute minimal :min.',
            'max' => ':attribute maksimal :max karakter.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'before_or_equal' => ':attribute tidak boleh melebihi hari ini.',
            'exists' => ':attribute tidak valid.',
            'in' => 'Tipe device tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'date' => 'Tanggal',
            'customer_name' => 'Nama Pelanggan',
            'customer_phone' => 'No HP',
            'device_type' => 'Tipe Device',
            'device_model' => 'Tipe Device',
            'issue_description' => 'Keluhan',
            'service_fee' => 'Biaya Jasa',
            'sparepart_cost' => 'Biaya Sparepart',
            'sparepart_description' => 'Keterangan Sparepart',
            'account_id' => 'Akun',
        ];
    }
}
