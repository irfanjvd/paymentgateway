<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SignupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(){
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(){
        $rules =  [];
        if(!empty($this->request->all())){
            $rules =  [
                'name' => 'required|max:150',
                'email'=>'required',
                'password' => 'required',
            ];
            
        }
        return $rules;    
        
    }
}
