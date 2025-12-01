<?php

namespace Assure\Workflow\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowConditionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'condition_type' => 'sometimes|nullable|string',
            'condition_id' => 'sometimes|nullable|string',
            'match_type' => 'sometimes|nullable|string',
            'name' => 'sometimes|nullable|string',
            'value' => 'sometimes|nullable|string',
            'data' => 'sometimes|nullable|array',
            'text' => 'sometimes|nullable|string',
            'workflow_show_step_id' => 'sometimes|nullable|integer',
        ];
    }
}

