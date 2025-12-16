<?php

namespace Assure\Workflow\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkflowRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:workflows|max:255',
            'description' => 'nullable|string',
        ];
    }
    
    public function messages()
    {
        return [
            'name.unique' => 'There is already a workflow called :input. Please use a different name.',
        ];
    }
}

