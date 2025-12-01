<?php

namespace Assure\Workflow\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkflowStepRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $workflowId = $this->route('workflowId');
        $stepId = $this->route('stepId');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                // unique within the same workflow, excluding the current step
                Rule::unique('workflow_steps', 'name')
                    ->where(function ($q) use ($workflowId) {
                        return $q->where('workflow_id', $workflowId);
                    })
                    ->ignore($stepId),
            ],
            'order' => 'sometimes|nullable|integer',
            'type' => 'sometimes|required|string',
            'module' => 'sometimes|required|string',
            'data' => 'sometimes|nullable',
            'data.formId' => 'sometimes|required_if:module,COMPOSER',
            'data.declineGoBack' => 'sometimes|required_if:module,APPROVAL',
            // 'data.formsToApprove' => 'sometimes|required_if:module,APPROVAL|array|min:1',
            'condition_citeria' => 'sometimes|nullable|string',
        ];
    }

    public function messages() 
    {
        return [
            'name.required' => 'The Step Name field is required.',
            'type.required' => 'The Step Type field is required.',
            'module.required' => 'The Module field is required.',
            'data.formId.required_if' => 'The Form Name field is required',
            'data.declineGoBack.required_if' => 'Resubmit field is required.',
            // 'data.formsToApprove.required_if' => 'Select Steps to Approve in required.',
            // 'data.formsToApprove.min' => 'At least one form must be selected in Forms to Approve.',
        ];
    }
}

