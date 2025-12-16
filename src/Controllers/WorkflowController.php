<?php

namespace Assure\Workflow\Controllers;

use Illuminate\Routing\Controller;
use Assure\Workflow\Models\Workflow;
use Assure\Workflow\Models\WorkflowInstance;
use Assure\Workflow\Models\WorkflowStep;
use Assure\Workflow\Models\WorkflowStepCondition;
use Assure\Workflow\Services\WorkflowEngine;
use Illuminate\Http\Request;
use Assure\Workflow\Requests\StoreWorkflowRequest;
use Assure\Workflow\Requests\UpdateWorkflowRequest;
use Assure\Workflow\Requests\StoreWorkflowStepRequest;
use Assure\Workflow\Requests\UpdateWorkflowStepRequest;
use Assure\Workflow\Requests\StoreWorkflowConditionRequest;
use Assure\Workflow\Requests\UpdateWorkflowConditionRequest;
use Assure\Workflow\Requests\ReorderWorkflowStepsRequest;
use Assure\Workflow\Requests\CloneWorkflowRequest;
use Illuminate\Support\Facades\DB;

class WorkflowController extends Controller
{
    private $engine;

    public function __construct(WorkflowEngine $engine)
    {
        $this->engine = $engine;
    }

    public function index()
    {
        $workflows = Workflow::paginate(20);
        return view('workflow::workflows.index', compact('workflows'));
    }

    public function start($id)
    {
        $workflow = Workflow::findOrFail($id);
        $instance = $this->engine->start($workflow);
        return redirect()->route('workflow.instances.show', $instance->id);
    }

    public function showInstance($id)
    {
        $instance = WorkflowInstance::findOrFail($id);
        return view('workflow::workflows.show', compact('instance'));
    }

    public function show($id)
    {
        $workflow = Workflow::findOrFail($id);
        
        return view('workflow::workflows.detail', compact('workflow'));
    }

    // API endpoints for Vue CRUD
    public function apiIndex(Request $request)
    {
        $query = Workflow::with(['steps.conditions'])->orderBy('id', 'desc');
        
        // Filter by name if provided
        if ($request->has('name') && $request->name) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        // Filter by account_id if provided
        if ($request->has('accountId') && $request->accountId) {
            $query->where('account_id', $request->accountId);
        }
        
        return $query->paginate(20);
    }

    public function apiShow($id)
    {
        $wf = Workflow::with(['steps' => function($q) {
            $q->orderBy('order'); // order steps by 'order' column
        }, 'steps.conditions' => function($q) {
            $q->orderBy('id'); // order conditions by id
        }])->findOrFail($id);

        $clientName = 'Cushman and Wakefield'; 

        if (config('app.env') == 'production') {
            $clientName = 'Chocolate Coded';
        }

        $webDb = config('database.connections.web.database');

        $base = DB::connection('jot')->table('forms as f')
            ->where('f.username', $clientName)
            ->where('f.status', '!=', 'DELETED')
            ->select('id', 'title');

        $unsorted = (clone $base)
            ->leftJoin("$webDb.categoryHasForm as chf", function ($join) {
                $join->on('chf.idForm', '=', 'f.id')
                    ->where('chf.idCategory', '>', 0); // only “valid” categories
            })
        ->whereNull('chf.idForm') // no valid category ⇒ empty/null/0
        ->select('f.*')
        ->orderBy('f.title', 'asc')
        ->get()
        ->unique('title')
        ->values();

        $wf['forms'] = $unsorted;

        $wf['stepTypes'] = config('workflow.step_types');

        return response()->json($wf);
    }

    public function fetchAuditorJotform($idForm)
    {
        // Fetch the form
        $form = DB::connection('jot')->table('forms')->where('id', $idForm)->first();
        if (!$form) {
            return false;
        }

        // Fetch form and question properties
        $formProperties = DB::connection('jot')->table('form_properties')->where('form_id', $idForm)->get();
        $questionProperties = DB::connection('jot')->table('question_properties')->where('form_id', $idForm)->get();

        if ($formProperties->isEmpty() || $questionProperties->isEmpty()) {
            return false;
        }

        // Initialize form array
        $formArr = [
            'title' => $form->title,
            'template' => $form->template,
            'id' => $form->id
        ];
        $questions = [];

        // Process form properties
        foreach ($formProperties as $prop) {
            if (in_array($prop->type, ['conditions', 'formStrings'])) {
                $value = $this->decodeValue($prop); // You can implement _decodeValue in your Laravel class
                if ($prop->type === 'conditions') {
                    $formArr[$prop->type][$prop->item_id][$prop->prop] = $value;
                } else {
                    $formArr[$prop->type][$prop->prop] = $value;
                }
            } else {
                $formArr[$prop->prop] = $prop->value;
            }
        }

        // Process question properties
        foreach ($questionProperties as $prop) {
            $value = $prop->value;

            if ($prop->prop === 'sublabels') {
                $decoded = json_decode($value, true);
                $value = $decoded !== null ? $decoded : $value;
            } elseif ($prop->prop === 'name') {
                $value = sprintf('q%d_%s', $prop->question_id, $value);
            } elseif ($prop->prop === 'options') {
                $value = str_replace(" |", "|", trim($value));
            }

            $questions[$prop->question_id][$prop->prop] = $value;
        }

        // Apply conditions
        if (isset($formArr['conditions']) && count($formArr['conditions'])) {
            foreach ($formArr['conditions'] as $condition) {
                if (isset($condition['action']->visibility) && $condition['action']->visibility === 'Show') {
                    $questions[$condition['action']->field]['Visibility'] = 'Hidden';
                }
            }
        }

        // Remove questions without qid
        foreach ($questions as $index => $question) {
            if (!isset($question['qid'])) {
                unset($questions[$index]);
            }
        }

        // Sort questions by order
        usort($questions, function ($a, $b) {
            return ($a['order'] ?? 0) - ($b['order'] ?? 0);
        });

        // Prepare i18n (if needed, optional)
        $i18n = [];
        // Example: load translations from files if required

        return [
            'form' => $formArr,
            'questions' => $questions,
            'i18n' => $i18n
        ];
    }

    public function decodeValue($property)
    {
        $value = json_decode($property->value, true); // decode as associative array
        return $value !== null ? $value : $property->value;
    }

    public function apiStore(StoreWorkflowRequest $request)
    {
        $data = $request->validated();
        $wf = Workflow::create($data);
        return response()->json($wf, 201);
    }

    public function apiUpdate($id, UpdateWorkflowRequest $request)
    {
        $wf = Workflow::findOrFail($id);
        $data = $request->validated();
        $wf->update($data);
        return response()->json($wf->fresh(['steps.conditions']));
    }

    public function apiDestroy($id)
    {
        $wf = Workflow::findOrFail($id);
        $wf->delete();
        return response()->json(['deleted' => true]);
    }

    /**
     * Clone a workflow including its steps and conditions.
     */
    public function apiClone($id, CloneWorkflowRequest $request)
    {
        $original = Workflow::with(['steps.conditions'])->findOrFail($id);

        $cloned = DB::transaction(function () use ($original, $request) {
            // Use the name from the request
            $newName = $request->validated()['name'];

            // Create workflow clone
            $newWorkflow = Workflow::create([
                'name' => $newName,
                'description' => $original->description,
                'config' => $original->config,
            ]);

            // Map old step id => new step id
            $stepIdMap = [];

            // Clone steps ordered by 'order' then id to keep order stable
            $steps = $original->steps->sortBy(function ($s) {
                return sprintf('%010d-%010d', (int)($s->order ?? 0), (int)$s->id);
            })->values();

            foreach ($steps as $s) {
                $newStep = WorkflowStep::create([
                    'workflow_id' => $newWorkflow->id,
                    'name' => $s->name,
                    'order' => $s->order,
                    'config' => $s->config,
                    'module' => $s->module,
                    'type' => $s->type,
                    'data' => $s->data,
                    'condition_citeria' => $s->condition_citeria,
                ]);
                $stepIdMap[$s->id] = $newStep->id;
            }

            // Clone conditions per step, remapping show_step references
            foreach ($steps as $s) {
                $targetStepId = $stepIdMap[$s->id] ?? null;
                if (!$targetStepId) {
                    continue;
                }
                $newStep = WorkflowStep::find($targetStepId);

                foreach ($s->conditions as $c) {
                    $mappedShowId = $c->workflow_show_step_id && isset($stepIdMap[$c->workflow_show_step_id])
                        ? $stepIdMap[$c->workflow_show_step_id]
                        : $c->workflow_show_step_id; // leave as-is if referencing outside cloned set

                    WorkflowStepCondition::create([
                        'workflow_step_id' => $newStep->id,
                        'condition_type' => $c->condition_type,
                        'condition_id' => $c->condition_id,
                        'match_type' => $c->match_type,
                        'name' => $c->name,
                        'value' => $c->value,
                        'data' => $c->data,
                        'text' => $c->text,
                        'workflow_show_step_id' => $mappedShowId,
                    ]);
                }
            }

            return $newWorkflow->load(['steps.conditions']);
        });

        // Ensure steps are sorted in response
        $cloned->steps = $cloned->steps->sortBy('order')->values();
        return response()->json($cloned, 201);
    }

    /**
     * Export workflow with steps and conditions as JSON.
     */
    public function apiExport($id)
    {
        $wf = Workflow::with(['steps.conditions'])->findOrFail($id);
        $export = [
            'meta' => [
                'version' => 1,
                'exported_at' => now()->toIso8601String(),
                'source' => config('app.name', 'assure'),
            ],
            'workflow' => [
                'name' => $wf->name,
                'description' => $wf->description,
                'config' => $wf->config,
            ],
            'steps' => [],
        ];

        $steps = $wf->steps->sortBy(function ($s) {
            return sprintf('%010d-%010d', (int)($s->order ?? 0), (int)$s->id);
        })->values();

        foreach ($steps as $s) {
            $stepArr = [
                'uid' => $s->id, // keep original id as uid for mapping on import
                'name' => $s->name,
                'order' => $s->order,
                'config' => $s->config,
                'module' => $s->module,
                'type' => $s->type,
                'data' => $s->data,
                'condition_citeria' => $s->condition_citeria,
                'conditions' => [],
            ];
            foreach ($s->conditions as $c) {
                $stepArr['conditions'][] = [
                    'condition_type' => $c->condition_type,
                    'condition_id' => $c->condition_id,
                    'match_type' => $c->match_type,
                    'name' => $c->name,
                    'value' => $c->value,
                    'data' => $c->data,
                    'text' => $c->text,
                    // export by referencing the original step uid to re-map later
                    'workflow_show_step_uid' => $c->workflow_show_step_id,
                ];
            }
            $export['steps'][] = $stepArr;
        }

        $filename = sprintf('workflow-%s-%s.json', preg_replace('/[^A-Za-z0-9_-]+/', '-', $wf->name), now()->format('Ymd-His'));
        return response()
            ->json($export)
            ->withHeaders([
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Content-Type' => 'application/json',
            ]);
    }

    /**
     * Import workflow JSON previously exported, recreating steps and conditions.
     * Accepts:
     * - multipart file field "file" (JSON file)
     * - JSON body with "data" containing the export payload
     */
    public function apiImport(\Illuminate\Http\Request $request)
    {
        // Load payload
        $payload = null;
        if ($request->hasFile('file')) {
            $payload = json_decode(file_get_contents($request->file('file')->getRealPath()), true);
        } else {
            $raw = $request->input('data', $request->input('workflow', null));
            if (is_string($raw)) {
                $payload = json_decode($raw, true);
            } elseif (is_array($raw)) {
                $payload = $raw;
            }
        }

        if (!$payload || !isset($payload['workflow']) || !isset($payload['steps']) || !is_array($payload['steps'])) {
            return response()->json(['message' => 'Invalid import payload.'], 422);
        }

        $wfData = $payload['workflow'];
        $stepsData = $payload['steps'];

        $new = DB::transaction(function () use ($wfData, $stepsData) {
            $baseName = $wfData['name'] ?? 'Imported Workflow';
            $newName = $baseName;
            $suffix = 2;
            while (Workflow::where('name', $newName)->exists()) {
                $newName = $baseName . " (Imported {$suffix})";
                $suffix++;
            }

            $newWf = Workflow::create([
                'name' => $newName,
                'description' => $wfData['description'] ?? null,
                'config' => $wfData['config'] ?? null,
            ]);

            $uidToNewStepId = [];
            // Create steps in provided order
            foreach ($stepsData as $s) {
                $newStep = WorkflowStep::create([
                    'workflow_id' => $newWf->id,
                    'name' => $s['name'] ?? 'Step',
                    'order' => $s['order'] ?? 0,
                    'config' => $s['config'] ?? null,
                    'module' => $s['module'] ?? null,
                    'type' => $s['type'] ?? null,
                    'data' => $s['data'] ?? null,
                    'condition_citeria' => $s['condition_citeria'] ?? null,
                ]);
                if (isset($s['uid'])) {
                    $uidToNewStepId[$s['uid']] = $newStep->id;
                }
            }

            // Create conditions with mapping
            foreach ($stepsData as $s) {
                $uid = $s['uid'] ?? null;
                $targetId = $uid ? ($uidToNewStepId[$uid] ?? null) : null;
                if (!$targetId) {
                    continue;
                }
                $newStep = WorkflowStep::find($targetId);
                foreach (($s['conditions'] ?? []) as $c) {
                    $showUid = $c['workflow_show_step_uid'] ?? null;
                    $mappedShowId = $showUid && isset($uidToNewStepId[$showUid]) ? $uidToNewStepId[$showUid] : null;
                    WorkflowStepCondition::create([
                        'workflow_step_id' => $newStep->id,
                        'condition_type' => $c['condition_type'] ?? null,
                        'condition_id' => $c['condition_id'] ?? null,
                        'match_type' => $c['match_type'] ?? null,
                        'name' => $c['name'] ?? null,
                        'value' => $c['value'] ?? null,
                        'data' => $c['data'] ?? null,
                        'text' => $c['text'] ?? null,
                        'workflow_show_step_id' => $mappedShowId,
                    ]);
                }
            }

            return $newWf->load(['steps.conditions']);
        });

        $new->steps = $new->steps->sortBy('order')->values();
        return response()->json($new, 201);
    }

    // Steps
    public function apiStoreStep($workflowId, StoreWorkflowStepRequest $request)
    {
        $wf = Workflow::findOrFail($workflowId);
        $payload = $request->validated();
        $payload['workflow_id'] = $wf->id;
        $step = WorkflowStep::create($payload);
        return response()->json($step, 201);
    }

    public function apiUpdateStep($workflowId, $stepId, UpdateWorkflowStepRequest $request)
    {
        $step = WorkflowStep::with('conditions')->where('workflow_id', $workflowId)->findOrFail($stepId);
        $payload = $request->validated();
        $step->update($payload);
        return response()->json($step);
    }

    public function apiDestroyStep($workflowId, $stepId)
    {
        $step = WorkflowStep::where('workflow_id', $workflowId)->findOrFail($stepId);
        $step->delete();
        return response()->json(['deleted' => true]);
    }

    public function apiReorderSteps($workflowId, ReorderWorkflowStepsRequest $request)
    {
        $wf = Workflow::findOrFail($workflowId);
        $ids = $request->validated()['order'];
        $order = 0;
        foreach ($ids as $id) {
            WorkflowStep::where('workflow_id', $wf->id)->where('id', $id)->update(['order' => $order++]);
        }
        return response()->json(['ok' => true]);
    }

    // Conditions
    public function apiStoreCondition($workflowId, $stepId, StoreWorkflowConditionRequest $request)
    {
        $step = WorkflowStep::where('workflow_id', $workflowId)->findOrFail($stepId);
        $payload = $request->validated();
        $payload['workflow_step_id'] = $step->id;
        $cond = WorkflowStepCondition::create($payload);
        return response()->json($cond, 201);
    }

    public function apiUpdateCondition($workflowId, $stepId, $conditionId, UpdateWorkflowConditionRequest $request)
    {
        $cond = WorkflowStepCondition::whereHas('step', function($q) use ($workflowId, $stepId){
            $q->where('workflow_id', $workflowId)->where('id', $stepId);
        })->findOrFail($conditionId);
        $payload = $request->validated();
        $cond->update($payload);
        return response()->json($cond);
    }

    public function apiDestroyCondition($conditionId)
    {
        $cond = WorkflowStepCondition::find($conditionId);
        $cond->delete();
        return response()->json(['deleted' => true]);
    }

    /**
     * Return form components for a given step.
     * It will look up the step, read its data->formId, and fetch components from the JOT connection.
     */
    public function apiFormComponents($workflowId, $formId)
    {

        $components = $this->fetchAuditorJotform($formId);

        return response()->json([
            'components' => $components,
        ]);
    }
}

