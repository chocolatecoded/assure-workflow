<?php

namespace Assure\Workflow\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkflowStepRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $workflowId = $this->route('workflowId');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // unique within the same workflow
                Rule::unique('workflow_steps', 'name')
                    ->where(function ($q) use ($workflowId) {
                        return $q->where('workflow_id', $workflowId)->whereNull('deleted_at');
                    }),
            ],
            'order' => 'nullable|integer',
            // required fields
            'type' => 'required|string',
            'module' => 'required|string',
            // nested data validations by module
            'data' => 'nullable',
            'data.formId' => 'required_if:module,COMPOSER',
            'data.declineGoBack' => 'required_if:module,APPROVAL',
            // 'data.formsToApprove' => 'required_if:module,APPROVAL|array|min:1',
            'condition_citeria' => 'nullable|string',
        ];
    }

    public function messages() 
    {
        return [
        //     'name.required' => 'The Step Name field is required.',
        //     'type.required' => 'The Step Type field is required.',
        //     'module.required' => 'The Module field is required.',
            'data.formId.required_if' => 'The Form Name field is required',
            // 'data.declineGoBack.required_if' => 'Resubmit field is required.',
            // 'data.formsToApprove.required_if' => 'Select Steps to Approve in required.',
            // 'data.formsToApprove.min' => 'At least one form must be selected in Forms to Approve.',
        ];
    }
}

