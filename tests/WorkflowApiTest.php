<?php

namespace Assure\Workflow\Tests;

use Assure\Workflow\Models\Workflow;
use Assure\Workflow\Models\WorkflowStep;
use App\Models\WorkOrders;
use App\Models\Company;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class WorkflowApiTest extends TestCase
{
//    use DatabaseTransactions;

    /**
     * Test GET request to api/v3/workflow/next-step/{workOrder}
     * Returns the first step when no submission exists
     */
    public function testGetNextStepReturnsFirstStepWhenNoSubmission()
    {
        // Create a company
//        $company = Company::create([
//            'company_id' => 'TEST001',
//            'name' => 'Test Company',
//        ]);
//
//        // Create a work order
//        $workOrder = WorkOrders::create([
//            'work_order_no' => 'WO-TEST-001',
//            'client_code' => $company->company_id,
//        ]);
        
        // Create a workflow with ID 30 (controller hardcodes find(30))
//        $workflow = Workflow::create([
//            'name' => 'Test Workflow',
//            'description' => 'Test Description',
//        ]);
        $workflow = Workflow::find(30);
//        dd($workflow->toArray());

        // Create a workflow step with formId
//        $firstStep = WorkflowStep::create([
//            'workflow_id' => $workflow->id,
//            'name' => 'First Step',
//            'order' => 1,
//            'data' => ['formId' => '123456'],
//        ]);
//
//        // Make GET request to the API endpoint
        $response = $this->get("/api/v3/workflow/next-step/758}");
        dd($response);
//        // Assert response
//        $response->assertStatus(200);
//        $response->assertJsonStructure([
//            'workflowNextStepForm'
//        ]);
//
//        $data = $response->json();
//        $this->assertNotEmpty($data['workflowNextStepForm']);
    }
}

