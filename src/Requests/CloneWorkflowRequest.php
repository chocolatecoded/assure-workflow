<?php

namespace Assure\Workflow\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloneWorkflowRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:workflows|max:255',
        ];
    }
    
    public function messages()
    {
        return [
            'name.unique' => 'Workflow name cannot be the same as an existing workflow',
            'name.required' => 'Workflow name is required',
        ];
    }
}

