<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;

class DModelRequest extends FormRequest
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
            'model_name' => 'required|unique:d_models|max:20',
            'data_split' => 'numeric|min:50|max:90',            
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
            'model_name.required' => 'Nama model tidak boleh kosong!',
            'model_name.unique' => 'Nama model sudah digunakan!',
            'model_name.max' => 'Nama tidak boleh lebih dari 20 karakter!',
            'data_split.min' => 'Jumlah data training minimal 50%!',
            'data_split.max' => 'Jumlah data training maksimal 90%!'
        ];
    }
}
