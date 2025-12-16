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
        $workflowId = $this->route('id'); // Get the workflow ID from the route
        
        return [
            'name' => 'required|string|max:255|unique:workflows,name,' . $workflowId,
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

