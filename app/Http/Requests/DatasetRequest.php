<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;

class DatasetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category' => 'required|min:3|max:20',
            'dataset' => 'required|mimes:xlsx,xls'
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'category.required' => 'Kategori tidak boleh kosong!',
            'category.min' => 'Kategori minimal 3 karakter!',
            'category.max' => 'Kategori maksimal 20 karakter!',            
            'dataset.required' => 'Dataset tidak boleh kosong!',
            'dataset.mimes' => 'Format file harus berekstensi xlsx atau xls',
        ];
    }
}
