<?php

namespace Assure\Workflow\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:workflows',
            'description' => 'nullable|string',
        ];
    }
}

