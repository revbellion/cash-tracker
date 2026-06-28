<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReturnPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:500'],
            'return_date' => ['required', 'date'],
            'refund_amount' => ['nullable', 'integer', 'min:0'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk harus dipilih.',
            'product_id.exists' => 'Produk tidak ditemukan.',
            'qty.required' => 'Jumlah retur harus diisi.',
            'qty.min' => 'Jumlah retur minimal 1.',
            'return_date.required' => 'Tanggal retur harus diisi.',
            'account_id.exists' => 'Akun tidak ditemukan.',
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => 'Produk',
            'qty' => 'Jumlah Retur',
            'reason' => 'Alasan',
            'return_date' => 'Tanggal Retur',
            'refund_amount' => 'Nilai Refund',
            'account_id' => 'Akun',
        ];
    }
}
